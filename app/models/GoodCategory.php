<?php

namespace PolombardamModels;

use Phalcon\Db\Result\Pdo;
use Phalcon\Di;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Resultset\Simple;
use Polombardam\StringsHelper;

class GoodCategory extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $parent_id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $name_translit;

    /**
     *
     * @var integer
     */
    public $sort;

    /**
     *
     * @var integer
     */
    public $system;

    public function initialize() {
        $this->setSource("good_category");

        $this->hasMany("id", Good::class, "category_id");
        $this->hasMany("id", GoodSubCategory::class, "parent_id");
    }

    /**
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     *
     * @param bool $only_system
     * @return GoodSubCategory[]
     */
    public function getSubCategories($only_system = false) {
        $subcategories_criteria = GoodSubCategory::query()
                ->where('parent_id = :parent_id:', ['parent_id' => $this->id])
                ->orderBy('sort DESC, name');

        if ($only_system) {
            $subcategories_criteria->andWhere('system = 1');
        }

        return $subcategories_criteria->execute();
    }

    /**
     *
     * @return GoodSubCategory[]
     */
    public function getSystemSubCategories() {
        return $this->getSubCategories(true);
    }

    /**
     *
     * @return bool
     */
    public function isSubCategory() {
        return (bool) $this->parent_id;
    }

    /**
     *
     * @return string
     */
    public function makeTransliteratedName() {
        return StringsHelper::translitRusStringToUrl($this->name);
    }

    /**
     *
     * @return $this
     */
    public function updateTransliteratedName() {
        $this->name_translit = $this->makeTransliteratedName();
        $this->save();

        return $this;
    }

    /**
     *
     * @return GoodCategory[]
     */
    public static function findSystemCategories() {
        return self::findAllCategories(true);
    }

    /**
     *
     * @param bool $only_system
     * @return GoodCategory[]
     */
    public static function findAllCategories($only_system = false) {
        $categories_criteria = self::query()
                ->where('parent_id = 0')
                ->orderBy('sort DESC, name');

        if ($only_system) {
            $categories_criteria->andWhere('system = 1');
        }

        return $categories_criteria->execute();
    }

    /**
     *
     * @link https://docs.phalconphp.com/en/latest/db-phql#using-raw-sql
     * @param array $conditions
     * @return self[]
     */
    public static function findByRawSql($conditions = null) {
        $joins = ($conditions && $conditions['joins'] ? $conditions['joins'] : '');
        $where_conditions = ($conditions && $conditions['conditions'] ? $conditions['conditions'] : '');
        $params = ($conditions && $conditions['params'] ? $conditions['params'] : []);

        // Base model
        $instance = new self();

        // A raw SQL statement
        $sql = "SELECT gc.* "
                . "FROM `" . $instance->getSource() . "` gc "
                . "$joins "
                . "WHERE $where_conditions";

        $statement = $instance->getReadConnection()
                ->getInternalHandler()
                ->prepare($sql);
        $statement->execute($params);

        // Do not use PHQL to make able using USE INDEX and other stuff that not supported by PHQL
        $pdo_result = new Pdo($instance->getReadConnection(), $statement, $sql, $params);

        // Execute the query
        return new Simple(
                null, $instance, $pdo_result
        );
    }

    /**
     *
     * @param array $category
     * @return array
     */
    public static function editFromApi($organizationId, $category) {
        $connection = Di::getDefault()->get('db');
        $category_id_new = null;

        $category_old = self::findFirst([
                    "name = :catname: AND parent_id = 0",
                    'bind' => [
                        'catname' => $category['data']['name_old'],
                    ]
        ]);

        if ($category_old) {
            $category_id_new = $category_old->id;

            $exist_category_new = self::findFirst([
                        "name = :catname: AND parent_id = 0",
                        'bind' => [
                            'catname' => $category['data']['name_new'],
                        ],
            ]);

            $category_orgs = Good::query()
                    ->distinct(true)
                    ->columns(['organization'])
                    ->where('category_id = :category_id:', ['category_id' => $category_old->id])
                    ->execute();

            if ($category_orgs->count() == 1) {
                if ($exist_category_new) {
                    $phql = "UPDATE `good` SET "
                            . "category_id = '" . (int) $exist_category_new->id . "' "
                            . "WHERE category_id = '" . (int) $category_old->id . "' "
                            . "AND organization = '" . (int) $organizationId . "'";
                    $update_goods = $connection->query($phql);

                    $subcategories_old = GoodSubCategory::find([
                                "parent_id = :parent_id:",
                                'bind' => [
                                    'parent_id' => $category_old->id,
                                ],
                    ]);

                    foreach ($subcategories_old as $subcategory_old) {
                        $exist_subcategory_new = self::findFirst([
                                    "name = :catname: AND parent_id = :parent_id:",
                                    'bind' => [
                                        'catname' => $subcategory_old->name,
                                        'parent_id' => $exist_category_new->id,
                                    ],
                        ]);

                        if ($exist_subcategory_new) {
                            $phql = "UPDATE `good` SET "
                                    . "subcategory_id = '" . (int) $exist_subcategory_new->id . "' "
                                    . "WHERE subcategory_id = '" . (int) $subcategory_old->id . "' "
                                    . "AND organization = '" . (int) $organizationId . "'";
                            $new_subcategory_update = $connection->query($phql);

                            if ($new_subcategory_update) {
                                $subcategory_old->delete();
                            }
                        }
                    }

                    $phql = "UPDATE `good_category` SET "
                            . "parent_id = '" . (int) $exist_category_new->id . "' "
                            . "WHERE parent_id = '" . (int) $category_old->id . "'";
                    $update_categories = $connection->query($phql);

                    if ($update_goods && $update_categories && $category_old->delete()) {
                        return [
                            [
                                true,
                                'category-edit',
                                $exist_category_new->id,
                                'Category exist edited and old deleted'
                            ]
                        ];
                    } else {
                        $message = 'Error update exist category';
                    }
                } else {
                    $category_old->name = $category['data']['name_new'];
                    $category_old->name_translit = StringsHelper::translitRusStringToUrl($category_old->name);

                    if ($category_old->save()) {
                        return [
                            [
                                true,
                                'category-edit',
                                $category_id_new,
                                'Category edited'
                            ]
                        ];
                    } else {
                        $message = 'Error update category';
                    }
                }
            } else {
                $category_org_goods = Good::query()
                        ->where('category_id = :category_id: AND organization = :organization:', [
                            'category_id' => $category_old->id,
                            'organization' => $organizationId,
                        ])
                        ->execute();

                if ($category_org_goods->count() > 0) {
                    if ($exist_category_new) {
                        $phql = "UPDATE `good` SET "
                                . "category_id = '" . (int) $exist_category_new->id . "' "
                                . "WHERE category_id = '" . (int) $category_old->id . "' "
                                . "AND organization = '" . (int) $organizationId . "'";
                        $update_goods = $connection->query($phql);

                        if ($update_goods) {
                            return [
                                [
                                    true,
                                    'category-edit',
                                    $category_id_new,
                                    'Category exist edited and updated goods'
                                ]
                            ];
                        } else {
                            $message = 'Error exist update goods';
                        }
                    } else {
                        $new_category = new self();
                        $new_category->name = $category['data']['name_new'];
                        $new_category->name_translit = StringsHelper::translitRusStringToUrl($new_category->name);

                        if ($new_category->save()) {
                            $category_id_new = (int) $new_category->id;

                            $category_org_goods = Good::query()
                                    ->distinct(true)
                                    ->columns(['subcategory_id'])
                                    ->where('category_id = :category_id: AND organization = :organization:', [
                                        'category_id' => $category_old->id,
                                        'organization' => $organizationId,
                                    ])
                                    ->execute();

                            foreach ($category_org_goods as $category_org_good) {
                                if ($category_org_good->subcategory_id) {
                                    $subcategory_old = GoodSubCategory::findFirst((int) $category_org_good->subcategory_id);

                                    $new_subcategory = new GoodSubCategory();
                                    $new_subcategory->name = $subcategory_old->name;
                                    $new_subcategory->name_translit = StringsHelper::translitRusStringToUrl($new_subcategory->name);
                                    $new_subcategory->parent_id = $category_id_new;

                                    if ($new_subcategory->save()) {
                                        $phql = "UPDATE `good` SET subcategory_id = '" . (int) $new_subcategory->id . "' WHERE subcategory_id = '" . (int) $subcategory_old->id . "' AND organization = '" . (int) $organizationId . "'";
                                        $new_subcategory_update = $connection->query($phql);
                                    }
                                }
                            }

                            $phql = "UPDATE `good` SET "
                                    . "category_id = '" . $category_id_new . "' "
                                    . "WHERE category_id = '" . (int) $category_old->id . "' "
                                    . "AND organization = '" . (int) $organizationId . "'";
                            $update_goods = $connection->query($phql);

                            if ($update_goods) {
                                return [
                                    [
                                        true,
                                        'category-edit',
                                        $category_id_new,
                                        'Create new category and updated goods'
                                    ]
                                ];
                            } else {
                                $message = 'Error update goods';
                            }
                        } else {
                            $message = 'Error create new category';
                        }
                    }
                } else {
                    $message = 'Goods from this category does not exist';
                }
            }
        } else {
            $category_id_new = $category['data']['name_old'];
            $message = 'Category not exist';
        }

        return [
            [
                false,
                'category-edit',
                $category_id_new,
                $message,
            ]
        ];
    }

    /**
     *
     * @return Criteria
     */
    public static function getSubCategoriesQuery() {
        $gsc_alias = GoodSubCategory::class;

        return GoodSubCategory::query()
                        ->columns([
                            "{$gsc_alias}.id",
                            "{$gsc_alias}.parent_id",
                            "{$gsc_alias}.name",
                            "{$gsc_alias}.name_translit",
                            "{$gsc_alias}.system",
                            "COUNT(g.id) as rowcount"
                        ])
                        ->innerJoin(Good::class, "g.subcategory_id = {$gsc_alias}.id", "g")
                        ->innerJoin(Merchant::class, "g.merchant = m.id", "m")
                        ->where("g.deleted IS NULL")
                        ->andWhere("g.hidden IS NULL")
                        ->andWhere("g.sold IS NULL")
                        ->andWhere("g.withdrawn IS NULL")
                        ->andWhere("m.deleted is NULL")
                        ->groupBy("{$gsc_alias}.id")
                        ->orderBy("{$gsc_alias}.sort DESC, {$gsc_alias}.name");
    }

    /**
     *
     * @param array $categories_id
     * @param string $city_name
     * @param array $categories_count
     * @return array
     */
    public static function getMenuSubCategoriesData($categories_id, $city_name = null, $categories_count = []) {
        $result = [];

        if (!empty($categories_id)) {
            $query = self::getSubCategoriesQuery();

            $query->inWhere(GoodSubCategory::class . '.parent_id', $categories_id);

            if ($city_name) {
                $query->andWhere('g.city = :city_name:', ['city_name' => $city_name]);
            }

            $subcategories = $query->execute();

            foreach ($subcategories as $subcategory) {
                $result[$subcategory->parent_id]['subcategory_count'] += (int) $subcategory->rowcount;

                if ($subcategory->system) {
                    $result[$subcategory->parent_id]['subcategories'][] = $subcategory;
                } else {
                    $result[$subcategory->parent_id]['subcategory_other_count'] += (int) $subcategory->rowcount;
                }
            }

            foreach ($categories_count as $cat_id => $cat_count) {
                $result[$cat_id]['total_without_subcat'] = (int) $cat_count - (int) $result[$cat_id]['subcategory_count'];
            }
        }

        return $result;
    }

    /**
     *
     * @param array $categories_id
     * @param int $merchant_id
     * @param array $categories_count
     * @return array
     */
    public static function getMerchantMenuSubCategoriesData($categories_id, $merchant_id, $categories_count = []) {
        $result = [];

        if (!empty($categories_id)) {
            $query = self::getSubCategoriesQuery();

            $subcategories = $query->inWhere(GoodSubCategory::class . '.parent_id', $categories_id)
                    ->andWhere('g.merchant = :merchant_id:', ['merchant_id' => $merchant_id])
                    ->execute();

            foreach ($subcategories as $subcategory) {
                $result[$subcategory->parent_id]['subcategory_count'] += (int) $subcategory->rowcount;
                $result[$subcategory->parent_id]['subcategories'][] = $subcategory;
            }

            foreach ($categories_count as $cat_id => $cat_count) {
                $result[$cat_id]['total_without_subcat'] = (int) $cat_count - (int) $result[$cat_id]['subcategory_count'];
            }
        }

        return $result;
    }

    /**
     *
     * @return int
     */
    public static function getIdJewelry() {
        $good_category = GoodCategory::findFirst([
                    'name = :name: AND parent_id = 0 AND system = 1',
                    'bind' => ['name' => 'Ювелирные изделия'],
        ]);

        return ($good_category ? $good_category->id : null);
    }

    /**
     *
     * @param string $category_name
     * @param bool|null $is_system
     * @return self|null
     */
    public static function getCategoryByName(string $category_name, ?bool $is_system = null): ?self
    {
        if (is_null($is_system)) {
            $bind = [];
            $system_category_substring_query = '';
        } else {
            $bind = ['is_system' => (int) $is_system];
            $system_category_substring_query = ' AND system = :is_system:';
        }

        $category = GoodCategory::findFirst([
                    'name = :category_name: AND parent_id = 0' . $system_category_substring_query,
                    'bind' => array_merge($bind, ['category_name' => $category_name]),
        ]);

        return ($category ?: null);
    }

}
