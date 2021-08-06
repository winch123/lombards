<?php

namespace PolombardamModels;

use Phalcon\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Phalcon\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Phalcon\Db\Result\Pdo;
use Phalcon\Di;
use Phalcon\Mvc\Model\Resultset\Simple;
use Polombardam\StringsHelper;

class Good extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $organization;

    /**
     *
     * @var integer
     */
    public $article;

    /**
     *
     * @var integer
     */
    public $merchant;

    /**
     *
     * @var integer
     */
    public $counter_all = 0;

    /**
     *
     * @var integer
     */
    public $counter_phone = 0;

    /**
     *
     * @var integer
     */
    public $price;

    /**
     *
     * @var integer
     */
    public $bonus;

    /**
     *
     * @var integer
     */
    public $sold;

    /**
     *
     * @var integer
     */
    public $withdrawn;

    /**
     *
     * @var integer
     */
    public $deleted;

    /**
     *
     * @var string
     */
    public $date;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $city;

    /**
     *
     * @var string
     */
    public $features = "";

    /**
     *
     * @var integer
     */
    public $image_studio;

    /**
     *
     * @var integer
     */
    public $category_id;

    /**
     *
     * @var integer
     */
    public $subcategory_id;

    /**
     *
     * @var string
     */
    public $currency;

    /**
     *
     * @var integer
     */
    public $metal_id;

    /**
     *
     * @var integer
     */
    public $metal_standart_id;

    /**
     *
     * @var integer
     */
    public $hidden;

    /**
     *
     * @var float
     */
    public $size;

    public function initialize() {
        /*
         * Спецификации завязаны на названии товара и профиле(сети ломбардов)
         * bug: https://github.com/phalcon/cphalcon/issues/12035
         * need manual source patch and recompile everywhere
         */
        $this->hasOne(["name", "organization"], GoodSpecification::class, ["good_name", "org_id"]);
        $this->hasOne("category_id", GoodCategory::class, "id");
        $this->hasOne("subcategory_id", GoodSubCategory::class, "id");
        $this->hasOne("image_studio", GoodStudioPhoto::class, "id");
        $this->belongsTo("metal_id", GoodMetal::class, "id");
        $this->belongsTo("metal_standart_id", GoodMetalStandart::class, "id");
        $this->belongsTo("merchant", Merchant::class, "id");
        $this->hasMany("id", GoodImage::class, "good_id");

        $this->setSource('good');
    }

    /**
     *
     * @return Merchant
     */
    public function getMerchant() {
        return $this->getRelated(Merchant::class);
    }

    /**
     *
     * @return GoodImage[]
     */
    public function getImages() {
        return $this->getRelated(GoodImage::class);
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
     * @return GoodSubCategory
     */
    public function getSubCategory() {
        return $this->getRelated(GoodSubCategory::class);
    }

    /**
     *
     * @return GoodMetal
     */
    public function getMetal() {
        return $this->getRelated(GoodMetal::class);
    }

    /**
     *
     * @return GoodMetalStandart
     */
    public function getMetalStandart() {
        return $this->getRelated(GoodMetalStandart::class);
    }

    /**
     *
     * @return string one of 'none', 'studio', 'self'
     */
    public function getImageType() {
        $images = $this->getImages();

        if (count($images) <= 0) {
            if ($this->image_studio == 0) {
                return 'none';
            } else {
                $file_path = APP_PATH . '/public/studio/' . $this->getRelated(GoodStudioPhoto::class)->file_name;

                if (file_exists($file_path)) {
                    return 'studio';
                } else {
                    return 'none';
                }
            }
            return 'none';
        } else {
            return 'self';
        }
    }

    /**
     *
     * @return string|null
     */
    public function getStudioImage() {
        $file = "/studio/" . $this->getRelated(GoodStudioPhoto::class)->file_name;
        $file_path = APP_PATH . '/public/' . $file;

        if (file_exists($file_path)) {
            return $file;
        } else {
            return null;
        }
    }

    /**
     * Ссылка на обложку(главную картинку) товара
     *
     * @deprecated
     * @return string
     */
    public function getImage() {
        return $this->getMainImageSrc();
    }

    /**
     *
     * @return string
     */
    public function getMainImagePreviewSrc() {
        return $this->getMainImageSrc(true);
    }

    /**
     *
     * @param bool $preview
     * @return string
     */
    public function getMainImageSrc($preview = false) {
        $type = $this->getImageType();

        if ($type == 'none') {
            return self::getDefaultImage();
        } elseif ($type == 'studio') {
            $photo = $this->getStudioImage();

            return ($photo ? $photo : self::getDefaultImage());
        } elseif ($type == 'self') {
            $images = $this->getImages();

            if ($images && !count($images)) {
                return self::getDefaultImage();
            } else {
                foreach ($images as $image) {
                    if ($image->main == 1) {
                        return ($preview ? $image->preview : $image->src);
                    }
                }

                return ($preview ? $images[0]->preview : $images[0]->src);
            }
        } else {
            // unknown image type
            return self::getDefaultImage();
        }
    }

    /**
     *
     * @param bool $preview
     * @return array
     */
    public function getUploadedImagesSrc($preview = false) {
        $images = $this->getImages();

        $src_array = [];

        if ($images && count($images)) {
            foreach ($images as $image) {
                if (count($src_array) < 3) {
                    $src_array[] = ($preview ? $image->preview : $image->src);
                } else {
                    break;
                }
            }
        }

        return $src_array;
    }

    /**
     *
     * @return string
     */
    public static function getDefaultImage() {
        return "/static/img/noimage.gif";
    }

    /**
     *
     * @param GoodImage[] $images
     */
    public function fillImages($images) {
        if (isset($this->id) && $this->id != 0) {
            $check_images = GoodImage::find("good_id = " . (int) $this->id);

            if ($check_images) {
                foreach ($check_images as $image_to_remove) {
                    $image_to_remove->delete();
                }
            }
        }

        /*
         * Если главных картинок несколько то делаем главной только первую
         */
        $main_image_flag = false;
        foreach ($images as &$image) {
            if (!$main_image_flag && $image['cover'] == 1) {
                $main_image_flag = true;
            } else {
                $image['cover'] = 0;
            }
        }

        // remove reference to avoid foreach bug
        unset($image);

        /*
         * Если главных картинок нет вообще, то главной делаем первую.
         */
        if (!$main_image_flag) {
            $images[0]['cover'] = 1;
        }

        foreach ($images as $image) {
            $new_image = new GoodImage();
            $new_image->good_id = $this->id;
            $new_image->src = $image['src'];
            $new_image->preview = $image['preview'];
            $new_image->main = (int) $image['cover'];

            $new_image->create();
        }
    }

    /**
     *
     * @return string
     */
    public function getUrl() {
        $city = $this->getMerchant()->getCity();

        if ($city) {
            return "/" . $city->name_translit . "/good/" . $this->id;
        } else {
            return "/good/" . $this->id;
        }
    }

    /**
     *
     * @return $this
     */
    public function fillStudioField() {

        if ($studio_photo = GoodStudioPhoto::findStudioPhoto($this->name)) {
            $this->image_studio = (int) $studio_photo->id;
        } else {
            $this->image_studio = 0;
        }

        return $this;
    }

    /**
     *
     * @return int
     */
    public function countBonus() {
        $bonus = 0;

        $images = $this->getImages();

        if ($images && count($images) > 0) {
            $bonus += count($images) * 2;
        } elseif ($this->image_studio > 0) {
            $bonus += 2;
        }

        if (!empty($this->features)) {
            $bonus += 1;
        }

        if (!empty($this->price)) {
            $bonus += 5;
        }

        return $bonus;
    }

    public function updateBonus() {
        $this->bonus = $this->countBonus();
    }

    /**
     *
     * @param int $num
     * @return self[]
     */
    public static function findLastGoods($num = 6) {
        $di = Di::getDefault();

        /** @var CacheAdapterInterface $file_cache */
        $file_cache = $di->get('fcache');
        $cache_key = 'index_page.last_goods_ids';

        $last_goods_ids = $file_cache->get($cache_key);

        if (is_array($last_goods_ids) && count($last_goods_ids)) {
            $g_alias = Good::class;

            $goods_criteria = Good::query()
                    ->innerJoin(Merchant::class, "mer.id = {$g_alias}.merchant", "mer")
                    ->where("{$g_alias}.id IN ({last_goods_ids:array})", ['last_goods_ids' => $last_goods_ids])
                    ->andWhere("{$g_alias}.deleted IS NULL")
                    ->andWhere("{$g_alias}.hidden IS NULL")
                    ->andWhere("{$g_alias}.sold IS NULL")
                    ->andWhere("{$g_alias}.withdrawn IS NULL")
                    ->andWhere("mer.deleted IS NULL")
                    ->orderBy("{$g_alias}.date DESC")
                    ->limit($num);

            $goods = $goods_criteria->execute();

            if (count($goods) < $num) {
                $file_cache->delete($cache_key);
            }
        } else {
            $goods = self::findByRawSql([
                        "conditions" => "g.deleted IS NULL "
                        . "AND g.hidden IS NULL "
                        . "AND g.sold IS NULL "
                        . "AND g.withdrawn IS NULL "
                        . "AND g.price > 0 "
                        . "AND EXISTS(SELECT mer.id FROM `merchant` mer WHERE mer.id = g.merchant AND mer.deleted IS NULL LIMIT 1) "
                        . "AND (g.image_studio > 0 OR EXISTS(SELECT gi.id FROM `good_image` gi WHERE gi.good_id = g.id LIMIT 1)) "
                        . "ORDER BY g.date DESC "
                        . "LIMIT " . (int) $num
            ]);

            foreach ($goods as $good) {
                $last_goods_ids[] = $good->getId();
            }

            $file_cache->set($cache_key, $last_goods_ids);
        }

        return $goods;
    }

    /**
     *
     * @param bool $only_system
     * @param bool $use_category_name
     * @param int $limit
     * @return self[]
     */
    public static function findLastGoodsByCategories($only_system = false, $use_category_name = false, $limit = 6) {
        if ($only_system) {
            $categories = GoodCategory::findSystemCategories();
        } else {
            $categories = GoodCategory::findAllCategories();
        }

        $g_alias = Good::class;

        $goods_by_categories = [];
        foreach ($categories as $category) {
            $goods_criteria = Good::query()
                    ->innerJoin(Merchant::class, "mer.id = {$g_alias}.merchant", "mer")
                    ->where("{$g_alias}.deleted IS NULL")
                    ->andWhere("{$g_alias}.hidden IS NULL")
                    ->andWhere("{$g_alias}.sold IS NULL")
                    ->andWhere("{$g_alias}.withdrawn IS NULL")
                    ->andWhere("{$g_alias}.category_id = :category_id:", ["category_id" => $category->id])
                    ->andWhere("mer.deleted IS NULL")
                    ->orderBy("{$g_alias}.bonus DESC, {$g_alias}.date DESC")
                    ->limit($limit);

            $goods = $goods_criteria->execute();

            foreach ($goods as $good) {
                if ($use_category_name) {
                    $goods_by_categories[$category->name][] = $good;
                } else {
                    $goods_by_categories[$category->id][] = $good;
                }
            }
        }

        return $goods_by_categories;
    }

    /**
     *
     * @param int $limit
     * @return self[]
     */
    public static function findLastGoodsBySystemCategories($limit = 6) {
        return self::findLastGoodsByCategories(true, true, $limit);
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
        $sql = "SELECT g.* "
                . "FROM `" . $instance->getSource() . "` g "
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
     * @return string
     */
    public function getDateCustom() {
        $diff_sec = time() - strtotime($this->date);
        $diff_days = $diff_sec / 86400;

        if ($diff_days > 1) {
            $date = date("d.m.Y", strtotime($this->date));
        } else {
            $date = date("d.m.Y H:i", strtotime($this->date));
        }

        return $date;
    }

    /**
     *
     * @param int $organization_id
     * @param array $good
     * @return array
     */
    public static function addFromApi($organization_id, $good) {
        $errors = [];

        $current_merchant = Merchant::findFirst(
                        "organization = " . (int) $organization_id . " AND " .
                        "workplace = " . (int) $good['data']['workplace']
        );

        if (!$current_merchant) {
            $errors[] = [
                false,
                'good-add',
                $organization_id . '-' . $good['data']['workplace'],
                'Mercant not exist'
            ];
        } else {
            $good_exist = Good::findFirst([
                        "conditions" => "organization = " . (int) $organization_id . " AND article = " . (int) $good['data']['article'],
                        "for_update" => true
            ]);

            if ($good_exist) {
                $exist = true;
                $new_good = $good_exist;
            } else {
                $exist = false;
                $new_good = new Good();
            }

            if (!isset($good['data']['sold'])) {
                $new_good->sold = null;
            }

            if (!isset($good['data']['withdrawn'])) {
                $new_good->withdrawn = null;
            }

            if (isset($good['data']['deleted'])) {
                $new_good->deleted = 1;
            } else {
                $new_good->deleted = null;
            }

            if (isset($good['data']['hidden']) && $good['data']['hidden'] == 1) {
                $new_good->hidden = 1;
                $new_good->hidden_reason = $good['data']['hidden_reason'];
            } else {
                $new_good->hidden = null;
            }

            if (!isset($good['data']['category'])) {
                $good['data']['category'] = 'Прочее';
            }

            // Категория
            $exist_category = GoodCategory::findFirst([
                        'name = :catname: AND parent_id = 0',
                        'bind' => ['catname' => $good['data']['category']]
            ]);

            if ($exist_category) {
                $new_good->category_id = $exist_category->id;
            } else {
                $new_category = new GoodCategory();
                $new_category->name = $good['data']['category'];
                $new_category->name_translit = StringsHelper::translitRusStringToUrl($new_category->name);

                if ($new_category->save()) {
                    $new_good->category_id = $new_category->id;
                } else {
                    $new_good->category_id = null;
                }
            }

            // Подкатегория
            if (isset($good['data']['subcategory']) && !empty($good['data']['subcategory'])) {
                $exist_subcategory = GoodSubCategory::findFirst([
                            'name = :catname: AND parent_id = :parent_id:',
                            'bind' => [
                                'catname' => $good['data']['subcategory'],
                                'parent_id' => $new_good->category_id,
                            ],
                ]);

                if ($exist_subcategory) {
                    $new_good->subcategory_id = $exist_subcategory->id;
                } else {
                    $new_subcategory = new GoodSubCategory();
                    $new_subcategory->name = $good['data']['subcategory'];
                    $new_subcategory->name_translit = StringsHelper::translitRusStringToUrl($new_subcategory->name);
                    $new_subcategory->parent_id = $new_good->category_id;

                    if ($new_subcategory->save()) {
                        $new_good->subcategory_id = $new_subcategory->id;
                    } else {
                        $new_good->subcategory_id = null;
                    }
                }
            } else {
                $new_good->subcategory_id = null;
            }

            // Драг. металлы
            $metal_name = $good['data']['metal_name'];
            $metal_standart_name = $good['data']['metal_standart_name'];

            if (!empty($metal_name) && !empty($metal_standart_name)) {
                $metal = GoodMetal::findFirst([
                            'name = :metal_name:',
                            'bind' => [
                                'metal_name' => $metal_name,
                            ],
                ]);

                if ($metal) {
                    $new_good->metal_id = $metal->id;
                } else {
                    $new_good_metal = new GoodMetal();
                    $new_good_metal->name = $metal_name;

                    if ($new_good_metal->save()) {
                        $new_good->metal_id = $new_good_metal->id;
                    } else {
                        $new_good->metal_id = null;
                    }
                }

                $metal_standart = GoodMetalStandart::findFirst([
                            'name = :metal_standart_name:',
                            'bind' => [
                                'metal_standart_name' => $metal_standart_name,
                            ],
                ]);

                if ($metal_standart) {
                    $new_good->metal_standart_id = $metal_standart->id;
                } else {
                    $new_good_metal_standart = new GoodMetalStandart();
                    $new_good_metal_standart->name = $metal_standart_name;

                    if ($new_good_metal_standart->save()) {
                        $new_good->metal_standart_id = $new_good_metal_standart->id;
                    } else {
                        $new_good->metal_standart_id = null;
                    }
                }
            }

            $new_good->name = (string) $good['data']['name'];
            $new_good->organization = (int) $good['data']['organization'];
            $new_good->merchant = (int) $current_merchant->id;
            $new_good->article = (int) $good['data']['article'];
            $new_good->price = (int) $good['data']['price'];
            // для корректной выборки минимальных и максимальных значений занулливаем 0
            $new_good->size = ((float) $good['data']['size'] == 0 ? null : (float) $good['data']['size']);
            $new_good->date = date('Y-m-d H:i:s');
            $new_good->city = (string) $good['data']['city'];
            $new_good->features = (string) $good['data']['features'];
            $new_good->currency = (string) $good['data']['currency'];
            $new_good->merchant = $current_merchant->id ? (int) $current_merchant->id : 0;

            $new_good->fillStudioField();

            if ($exist) {
                if ($new_good->save()) {

                    $errors[] = [
                        true,
                        'good-add',
                        $good['data']['article'],
                        'Good created from old id'
                    ];
                } else {
                    $errors[] = [
                        false,
                        'good-add',
                        $good['data']['article'],
                        $new_good->getMessagesNormalized()
                    ];
                }
            } else {
                if ($new_good->create()) {
                    $errors[] = [
                        true,
                        'good-add-create',
                        $good['data']['article'],
                        'good created NEW'
                    ];
                } else {
                    $errors[] = [
                        false,
                        'good-add-create',
                        $good['data']['article'],
                        $new_good->getMessagesNormalized()
                    ];
                }
            }

            $new_good->fillImages($good['data']['images']);
            $new_good->updateBonus();
            $new_good->save();
        }

        return $errors;
    }

    /**
     *
     * @param int $organization_id
     * @param array $good
     * @return array
     */
    public static function removeFromApi($organization_id, $good) {
        $errors = [];

        $goods_to_remove = Good::findFirst("article = " . (int) $good['article'] . " AND organization = " . (int) $organization_id);

        if ($goods_to_remove) {
            $goods_to_remove->deleted = 1;
            $goods_to_remove->name = null;
            $goods_to_remove->price = null;
            $goods_to_remove->size = null;
            $goods_to_remove->date = '0000-00-00 00:00:00';
            $goods_to_remove->city = null;
            $goods_to_remove->features = null;
            $goods_to_remove->category_id = null;
            $goods_to_remove->subcategory_id = null;
            $goods_to_remove->currency = null;

            if ($goods_to_remove->save()) {
                $errors[] = [
                    true,
                    'good-remove',
                    $good['article'],
                    'Good marked as removed (and ids saved)'
                ];
            } else {
                $errors[] = [
                    false,
                    'good-remove',
                    $good['article'],
                    $goods_to_remove->getMessagesNormalized()
                ];
            }
        } else {
            $errors[] = [
                false,
                'good-remove',
                $organization_id . '-' . $good['article'],
                'Good not exist'
            ];
        }

        return $errors;
    }

    /**
     *
     * @param int $organization_id
     * @param array $good
     * @return array
     */
    public static function editFromApi($organization_id, $good) {
        $errors = [];

        $good_to_edit = Good::findFirst([
                    "conditions" => "article = " . (int) $good['article'] . " AND organization = " . (int) $organization_id,
                    "for_update" => true
        ]);

        $current_merchant = Merchant::findFirst("organization = " . (int) $organization_id . " AND workplace = " . (int) $good['data']['workplace']);

        if (!$current_merchant) {
            $errors[] = [
                false,
                'good-edit',
                $organization_id . '-' . $good['data']['workplace'],
                'Merchant not exist'
            ];

            return $errors;
        }

        $is_new_good = false;

        if ($good_to_edit) {
            $good_to_edit->merchant = $current_merchant->id;
        } else {
            $errors[] = [
                false,
                'good-edit',
                $good['article'],
                'Good not exist'
            ];

            $good_to_edit = new Good();
            $good_to_edit->article = (int) $good['article'];
            $good_to_edit->merchant = $current_merchant->id;

            $is_new_good = true;
        }

        $good_to_edit->deleted = null;

        if (isset($good['data']['sold'])) {
            $good_to_edit->sold = $good['data']['sold'] == 0 ? null : (int) $good['data']['sold'];
        } else {
            $good_to_edit->sold = null;
        }

        if (isset($good['data']['withdrawn'])) {
            $good_to_edit->withdrawn = $good['data']['withdrawn'] == 0 ? null : (int) $good['data']['withdrawn'];
        } else {
            $good_to_edit->withdrawn = null;
        }

        if (isset($good['data']['name'])) {
            $good_to_edit->name = (string) $good['data']['name'];
        }

        if (isset($good['data']['organization'])) {
            $good_to_edit->organization = (string) $good['data']['organization'];
        }

        if (isset($good['data']['price'])) {
            $good_to_edit->price = (int) $good['data']['price'];
        }

        if (isset($good['data']['size'])) {
            // для корректной выборки минимальных и максимальных значений занулливаем 0
            $good_to_edit->size = ((float) $good['data']['size'] == 0 ? null : (float) $good['data']['size']);
        }

        if (isset($good['data']['date'])) {
            $good_to_edit->date = date('Y-m-d H:i:s');
        }

        if (isset($good['data']['city'])) {
            $good_to_edit->city = (string) $good['data']['city'];
        }

        if (isset($good['data']['features'])) {
            $good_to_edit->features = (string) $good['data']['features'];
        }

        if (isset($good['data']['hidden']) && $good['data']['hidden'] == 1) {
            $good_to_edit->hidden = 1;
            $good_to_edit->hidden_reason = $good['data']['hidden_reason'];
        } else {
            $good_to_edit->hidden = null;
        }

        if (!isset($good['data']['category'])) {
            $good['data']['category'] = 'Прочее';
        }

        // Категория
        $exist_category = GoodCategory::findFirst([
                    'name = :catname: AND parent_id = 0',
                    'bind' => ['catname' => $good['data']['category']]
        ]);

        if ($exist_category) {
            $good_to_edit->category_id = $exist_category->id;
        } else {
            $new_category = new GoodCategory();
            $new_category->name = $good['data']['category'];
            $new_category->name_translit = StringsHelper::translitRusStringToUrl($new_category->name);

            if ($new_category->save()) {
                $good_to_edit->category_id = $new_category->id;
            } else {
                $good_to_edit->category_id = null;
            }
        }

        // Подкатегория
        if (isset($good['data']['subcategory']) && !empty($good['data']['subcategory'])) {
            $exist_subcategory = GoodSubCategory::findFirst([
                        'name = :catname: AND parent_id = :parent_id:',
                        'bind' => [
                            'catname' => $good['data']['subcategory'],
                            'parent_id' => $good_to_edit->category_id,
                        ],
            ]);

            if ($exist_subcategory) {
                $good_to_edit->subcategory_id = $exist_subcategory->id;
            } else {
                $new_subcategory = new GoodSubCategory();
                $new_subcategory->name = $good['data']['subcategory'];
                $new_subcategory->name_translit = StringsHelper::translitRusStringToUrl($new_subcategory->name);
                $new_subcategory->parent_id = $good_to_edit->category_id;

                if ($new_subcategory->save()) {
                    $good_to_edit->subcategory_id = $new_subcategory->id;
                } else {
                    $good_to_edit->subcategory_id = null;
                }
            }
        } else {
            $good_to_edit->subcategory_id = null;
        }

        // Драг. металлы
        $metal_name = $good['data']['metal_name'];
        $metal_standart_name = $good['data']['metal_standart_name'];

        if (!empty($metal_name) && !empty($metal_standart_name)) {
            $metal = GoodMetal::findFirst([
                        'name = :metal_name:',
                        'bind' => [
                            'metal_name' => $metal_name,
                        ],
            ]);

            if ($metal) {
                $good_to_edit->metal_id = $metal->id;
            } else {
                $new_good_metal = new GoodMetal();
                $new_good_metal->name = $metal_name;

                if ($new_good_metal->save()) {
                    $good_to_edit->metal_id = $new_good_metal->id;
                } else {
                    $good_to_edit->metal_id = null;
                }
            }

            $metal_standart = GoodMetalStandart::findFirst([
                        'name = :metal_standart_name:',
                        'bind' => [
                            'metal_standart_name' => $metal_standart_name,
                        ],
            ]);

            if ($metal_standart) {
                $good_to_edit->metal_standart_id = $metal_standart->id;
            } else {
                $new_good_metal_standart = new GoodMetalStandart();
                $new_good_metal_standart->name = $metal_standart_name;

                if ($new_good_metal_standart->save()) {
                    $good_to_edit->metal_standart_id = $new_good_metal_standart->id;
                } else {
                    $good_to_edit->metal_standart_id = null;
                }
            }
        }

        if (isset($good['data']['currency'])) {
            $good_to_edit->currency = (string) $good['data']['currency'];
        }

        $good_to_edit->fillStudioField();

        if ($current_merchant) {
            $current_merchant->modified = date("Y-m-d H:i:s");
            $current_merchant->save();
        }

        if ($is_new_good) {
            if ($good_to_edit->create()) {
                $errors[] = [
                    true,
                    'good-edit-create',
                    $good['article'],
                    'Good created by using edit info'
                ];
            } else {
                $errors[] = [
                    false,
                    'good-edit-create',
                    $good['article'],
                    $good_to_edit->getMessagesNormalized()
                ];
            }
        } else {
            if ($good_to_edit->save()) {
                $errors[] = [
                    true,
                    'good-edit',
                    $good['article']
                ];
            } else {
                $errors[] = [
                    false,
                    'good-edit-cantsave',
                    $good['article'],
                    $good_to_edit->getMessagesNormalized()
                ];
            }
        }

        $good_to_edit->fillImages($good['data']['images']);
        $good_to_edit->updateBonus();
        $good_to_edit->save();

        return $errors;
    }

    /**
     *
     * @return int
     */
    public static function getTotalVisibleCount() {
        $di = Di::getDefault();

        /** @var CacheAdapterInterface $file_cache */
        $file_cache = $di->get('fcache');

        $goods_count = $file_cache->get('counters.total_goods');

        if (!isset($goods_count)) {
            /** @var DbAdapterInterface $db */
            $db = $di->get('db');

            $goods_count_result = $db->query(
                            "SELECT COUNT(g.id) as `count` "
                            . "FROM `good` g "
                            // Using index hints to speed up query (up to 10x times)
                            . "USE INDEX (`visible_goods`) "
                            . "INNER JOIN `merchant` mer ON g.merchant = mer.id "
                            . "INNER JOIN `good_category` cat ON g.category_id = cat.id "
                            . "WHERE g.`deleted` IS NULL "
                            . "AND mer.deleted IS NULL "
                            . "AND g.`hidden` IS NULL "
                            . "AND g.`sold` IS NULL "
                            . "AND g.`withdrawn` IS NULL "
                            . "LIMIT 1"
                    )
                    ->fetch();

            $goods_count = ($goods_count_result ? $goods_count_result['count'] : 0);

            $file_cache->set('counters.total_goods', $goods_count, 60 * 60);
        }

        return (int) $goods_count;
    }

}
