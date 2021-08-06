<?php

namespace PolombardamModels;

use Phalcon\Db\Result\Pdo;
use Phalcon\Di;
use Phalcon\Mvc\Model\Resultset\Simple;
use Polombardam\StringsHelper;

class GoodSubCategory extends ModelBase {

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

        $this->hasMany("id", Good::class, "subcategory_id");
        $this->belongsTo("parent_id", GoodCategory::class, "id");
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
     * @return GoodCategory
     */
    public function getCategory() {
        return $this->getRelated(GoodCategory::class);
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
        $sql = "SELECT gsc.* "
                . "FROM `" . $instance->getSource() . "` gsc "
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
     * @param array $subcategory
     * @return array
     */
    public static function editFromApi($organizationId, $subcategory) {
        $connection = Di::getDefault()->get('db');
        $subcategory_id_new = null;

        $category_old = self::findFirst([
                    "name = :catname: AND parent_id = 0",
                    'bind' => [
                        'catname' => $subcategory['data']['category_name'],
                    ]
        ]);

        if ($category_old) {
            $subcategory_old = self::findFirst([
                        "name = :catname: AND parent_id = :parent_id:",
                        'bind' => [
                            'catname' => $subcategory['data']['name_old'],
                            'parent_id' => $category_old->id,
                        ]
            ]);

            if ($subcategory_old) {
                $subcategory_id_new = $subcategory_old->id;

                $subcategory_goods = Good::query()
                        ->distinct(true)
                        ->columns(['organization'])
                        ->where('category_id = :category_id: AND subcategory_id = :subcategory_id:', [
                            'category_id' => $category_old->id,
                            'subcategory_id' => $subcategory_old->id,
                        ])
                        ->execute();

                if ($subcategory_goods->count() == 1) {
                    $exist_subcategory_new = self::findFirst([
                                "name = :catname: AND parent_id = :parent_id:",
                                'bind' => [
                                    'catname' => $subcategory['data']['name_new'],
                                    'parent_id' => $category_old->id,
                                ]
                    ]);

                    if ($exist_subcategory_new) {
                        $phql = "UPDATE `good` SET "
                                . "subcategory_id = '" . (int) $exist_subcategory_new->id . "' "
                                . "WHERE subcategory_id = '" . (int) $subcategory_old->id . "' "
                                . "AND organization = '" . (int) $organizationId . "'";
                        $update_goods = $connection->query($phql);

                        if ($update_goods && $subcategory_old->delete()) {
                            return [
                                [
                                    true,
                                    'subcategory-edit',
                                    $exist_subcategory_new->id,
                                    'Subcategory exist edited and old deleted'
                                ]
                            ];
                        } else {
                            $message = 'Error update exist category';
                        }
                    } else {
                        $subcategory_old->name = $subcategory['data']['name_new'];
                        $subcategory_old->name_translit = StringsHelper::translitRusStringToUrl($subcategory_old->name);

                        if ($subcategory_old->save()) {
                            return [
                                [
                                    true,
                                    'subcategory-edit',
                                    $subcategory_id_new,
                                    'Subcategory edited'
                                ]
                            ];
                        } else {
                            $message = 'Error update subcategory';
                        }
                    }
                } else {
                    $subcategory_org_goods = Good::query()
                            ->where('category_id = :category_id: AND subcategory_id = :subcategory_id: AND organization = :organization:', [
                                'category_id' => $category_old->id,
                                'subcategory_id' => $subcategory_old->id,
                                'organization' => $organizationId,
                            ])
                            ->execute();

                    if ($subcategory_org_goods->count() > 0) {
                        $new_subcategory = new self();
                        $new_subcategory->name = $subcategory['data']['name_new'];
                        $new_subcategory->name_translit = StringsHelper::translitRusStringToUrl($new_subcategory->name);
                        $new_subcategory->parent_id = $category_old->id;

                        if ($new_subcategory->save()) {
                            $subcategory_id_new = $new_subcategory->id;

                            $phql = "UPDATE `good` SET "
                                    . "subcategory_id = '" . (int) $subcategory_id_new . "' "
                                    . "WHERE subcategory_id = '" . (int) $subcategory_old->id . "' "
                                    . "AND organization = '" . (int) $organizationId . "'";
                            $update_goods = $connection->query($phql);

                            if ($update_goods) {
                                return [
                                    [
                                        true,
                                        'subcategory-edit',
                                        $subcategory_id_new,
                                        'Create new subcategory and updated goods'
                                    ]
                                ];
                            } else {
                                $message = 'Error update goods';
                            }
                        } else {
                            $message = 'Error create new subcategory';
                        }
                    } else {
                        $message = 'Goods from this subcategory does not exist';
                    }
                }
            } else {
                $subcategory_id_new = $subcategory['data']['name_old'];
                $message = 'Subcategory not exist';
            }
        } else {
            $subcategory_id_new = $subcategory['data']['category_name'];
            $message = 'Category not exist';
        }

        return [
            [
                false,
                'subcategory-edit',
                $subcategory_id_new,
                $message,
            ]
        ];
    }

    /**
     *
     * @param string $sub_category_name
     * @param string $category_name
     * @param bool|null $is_system
     * @return self|null
     */
    public static function getSubCategoryByName(string $sub_category_name, string $category_name, ?bool $is_system = null): ?self
    {
        $criteria = GoodSubCategory::query()
                ->innerJoin(GoodCategory::class, GoodSubCategory::class . '.parent_id = cat.id', 'cat')
                ->andWhere(GoodSubCategory::class . '.name = :sub_category_name:', ['sub_category_name' => $sub_category_name])
                ->andWhere('cat.parent_id = 0')
                ->andWhere('cat.name = :category_name:', ['category_name' => $category_name]);

        if (!is_null($is_system)) {
            $criteria->andWhere(GoodSubCategory::class . '.system = :is_system:', ['is_system' => (int) $is_system]);
        }

        $sub_category = $criteria->execute()->getFirst();

        return $sub_category;
    }

}
