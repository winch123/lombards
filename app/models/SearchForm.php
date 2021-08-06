<?php

namespace PolombardamModels;

use ControllerBase;
use Polombardam\CacheWrapper;

class SearchForm
{

    /**
     * Модуль округления размеров ювелирки
     */
    const JEWELRY_SIZE_STEP = 0.5;

    /**
     *
     * @var ControllerBase
     */
    public $context;

    /**
     *
     * @var string
     */
    public $search_text;

    /**
     *
     * @var bool
     */
    public $show_custom_categories;

    /**
     *
     * @var bool
     */
    public $show_custom_subcategories;

    /**
     *
     * @var bool
     */
    public $show_without_subcategories;

    /**
     *
     * @var bool
     */
    public $show_only_with_price = true;

    /**
     *
     * @var int
     */
    public $price_min;

    /**
     *
     * @var int
     */
    public $price_max;

    /**
     *
     * @var bool
     */
    public $show_only_with_size = false;

    /**
     *
     * @var int
     */
    public $size_min;

    /**
     *
     * @var int
     */
    public $size_max;

    /**
     *
     * @var City[]
     */
    public $cities_list = [];

    /**
     *
     * @var Merchant[]
     */
    public $merchant_list = [];

    /**
     *
     * @var GoodCategory[]
     */
    public $categories_list = [];

    /**
     *
     * @var GoodSubCategory[]
     */
    public $subcategories_list = [];

    /**
     *
     * @var GoodSubCategory[]
     */
    public $additional_subcategories_list = [];

    /**
     *
     * @var bool
     */
    public $show_filter_size;

    /**
     *
     * @var GoodMetal[]
     */
    public $metals_list = [];

    /**
     *
     * @var GoodMetalStandart[]
     */
    public $metal_standarts_list = [];

    /**
     *
     * @var ControllerBase $controller
     */
    public function __construct(ControllerBase $controller)
    {
        $this->context = $controller;
    }

    /**
     *
     * @todo переписать нафиг
     * @param callable $function
     * @param array $dependencies_context     *
     * @param array $dependencies_local
     * @param bool $do_cache
     */
    private function cachedGenerateData(callable $function, array $dependencies_context, array $dependencies_local = [], bool $do_cache = true): void
    {
        // фильтруем нужные объекты из контроллера
        $context = array_filter((array) $this->context, function ($key) use ($dependencies_context) {
            return in_array($key, $dependencies_context);
        }, ARRAY_FILTER_USE_KEY);

        // данные из текущего класса
        foreach ($dependencies_local as $param_name) {
            $context[$param_name] = $this->$param_name;
        }

        if ($do_cache) {
            $cache_lifetime = $this->context
                ->getDI()
                ->get('config')['application']['cacheSearchFormInSeconds'];

            $cache = new CacheWrapper($cache_lifetime);

            $cache_key = $function[1] . md5(serialize($context));

            $result = $cache->get($cache_key, function () use ($function, $context) {
                return $function($context);
            });
        } else {
            $result = $function($context);
        }

        // результаты сохраняем в поля $this, для дальнейшего использования в шаблонизаторе.
        foreach ($result as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     *
     * @return $this
     */
    public function generate()
    {
        $this->cachedGenerateData([$this, 'generateCitiesList'], ['current_city', 'current_country']);
        $this->cachedGenerateData([$this, 'generateMerchantList'], ['current_city']);
        $this->cachedGenerateData([$this, 'generateCategoriesList'], ['current_category', 'current_merchant', 'current_metal', 'current_merchant_network']);
        $this->cachedGenerateData([$this, 'generateIntervalData'],
                ['current_category', 'current_subcategory', 'current_merchant', 'current_city', 'current_metal', 'current_metal_standart', 'current_merchant_network'],
                ['show_custom_categories', 'show_custom_subcategories', 'show_without_subcategories'],
                empty($this->search_text));

        return $this;
    }

    /**
     *
     * @param array $data
     * @return array
     */
    private function generateIntervalData($data)
    {
        list('current_category' => $current_category,
            'current_subcategory' => $current_subcategory,
            'current_merchant' => $current_merchant,
            'current_city' => $current_city,
            'current_metal' => $current_metal,
            'current_metal_standart' => $current_metal_standart,
            'current_merchant_network' => $current_merchant_network) = $data;

        $g_alias = Good::class;

        $criteria = Good::query()
                ->columns("MIN(IF(price > 0, price, NULL)) AS min_price, "
                        . "MAX(IF(price > 0, price, NULL)) AS max_price, "
                        . "MIN(IF(size > 0, size, NULL)) AS min_size, "
                        . "MAX(IF(size > 0, size, NULL)) AS max_size")
                ->where("deleted IS NULL")
                ->andWhere("hidden IS NULL")
                ->andWhere("sold IS NULL")
                ->andWhere("withdrawn IS NULL")
                ->andWhere("(price > 0 OR size > 0)");

        if ($current_city) {
            $criteria->andWhere("city = :city_name:", ["city_name" => $current_city->name]);
        }

        if ($current_merchant) {
            $criteria->andWhere("merchant = :merchant_id:", ["merchant_id" => $current_merchant->id]);
        } elseif ($current_merchant_network) {
            $criteria->andWhere("organization = :organization_id:", ["organization_id" => $current_merchant_network->id]);
        }

        if ($this->show_custom_categories) {
            $criteria->innerJoin(GoodCategory::class, "{$g_alias}.category_id = cat.id", "cat")
                    ->andWhere("cat.system = 0");
        } else {
            if ($current_category) {
                $criteria->andWhere("category_id = :category_id:", ["category_id" => $current_category->id]);
            }

            if ($this->show_custom_subcategories) {
                $criteria->innerJoin(GoodSubCategory::class, "{$g_alias}.subcategory_id = subcat.id", "subcat")
                        ->andWhere("subcat.system = 0");
            } elseif ($this->show_without_subcategories) {
                $criteria->andWhere("subcategory_id IS NULL");
            } elseif ($current_subcategory) {
                $criteria->andWhere("subcategory_id = :subcategory_id:", ["subcategory_id" => $current_subcategory->id]);
            }
        }

        if ($this->search_text) {
            $criteria->andWhere("CONCAT(' ', {$g_alias}.name) LIKE :good_name:", ['good_name' => '% ' . $this->search_text . '%']);
        }

        if ($current_metal) {
            $criteria->andWhere("metal_id = :metal_id:", ["metal_id" => $current_metal->id]);

            if ($current_metal_standart) {
                $criteria->andWhere("metal_standart_id = :metal_standart_id:", ["metal_standart_id" => $current_metal_standart->id]);
            }
        }

        $intervals = $criteria->execute()->getFirst();

        $price_min = ($intervals ? $intervals->min_price : 0);
        $price_max = ($intervals ? $intervals->max_price : 0);

        $jewelry_size_step = self::JEWELRY_SIZE_STEP;

        // Минимум округляем в меньшую сторону с точностью по константе
        $size_min = ($intervals ? floor($intervals->min_size / $jewelry_size_step) * $jewelry_size_step : 0);
        // Максимум округляем в большую сторону с точностью по константе
        $size_max = ($intervals ? ceil($intervals->max_size / $jewelry_size_step) * $jewelry_size_step : 0);

        if ($current_subcategory && $current_subcategory->parent_id == GoodCategory::getIdJewelry() && ($size_min || $size_max)) {
            $show_filter_size = true;
        } else {
            $show_filter_size = false;
        }

        return [
            'price_min' => $price_min,
            'price_max' => $price_max,
            'size_min' => $size_min,
            'size_max' => $size_max,
            'jewelry_size_step' => $jewelry_size_step,
            'show_filter_size' => $show_filter_size,
        ];
    }

    /**
     *
     * @param array $data
     * @return array
     */
    private function generateCitiesList($data)
    {
        list('current_city' => $current_city,
            'current_country' => $current_country) = $data;

        $c_alias = City::class;

        $cities_query = City::query()
                ->columns([
                    $c_alias.'.id',
                    $c_alias.'.name',
                    $c_alias.'.name_translit',
                    ])
                ->join(Merchant::class, "{$c_alias}.name = mer.city", "mer", "LEFT")
                ->where("("
                        . "mer.custom IS NULL "
                        . "AND mer.deleted IS NULL "
                        . "AND mer.count_good > 0"
                        . ")"
                        // Если выбран город - принудительно отображаем его в списке
                        . "OR {$c_alias}.id = :city_id:", ["city_id" => (int) ($current_city ? $current_city->id : null)]
                )
                ->groupBy("{$c_alias}.id")
                ->orderBy("{$c_alias}.name ASC");


        if ($current_country) {
            $cities_query->andWhere("{$c_alias}.country_id = :country_id:", ["country_id" => (int) $current_country->id]);
        }

        $cities = $cities_query->execute();

        return ['cities_list' => $cities];
    }

    /**
     *
     * @param array $data
     * @return array
     */
    private function generateMerchantList($data)
    {
        $current_city = $data['current_city'];

        if ($current_city) {
            $m_alias = Merchant::class;

            $merchants = Merchant::query()
                    ->columns([
                        "{$m_alias}.id",
                        "{$m_alias}.name",
                        "network.id as merchant_network_id",
                        "network.merchant_name as merchant_network_name",
                    ])
                    ->innerJoin(Organization::class, "network.id = organization", "network")
                    ->where("custom IS NULL")
                    ->andWhere("deleted IS NULL")
                    ->andWhere("city = :city_name:", ["city_name" => $current_city->name])
                    ->orderBy("network.merchant_name DESC, {$m_alias}.name, {$m_alias}.id")
                    ->execute();

            $grouped_merchants = [];

            foreach ($merchants as $merchant) {
                if ($merchant->merchant_network_name) {
                    $grouped_merchants[$merchant->merchant_network_id]['merchant_name'] = 'Все филиалы сети ' . $merchant->merchant_network_name;
                    $grouped_merchants[$merchant->merchant_network_id]['merchants'][] = $merchant;
                } else {
                    $grouped_merchants['standalone']['merchant_name'] = 'Одиночные филиалы';
                    $grouped_merchants['standalone']['merchants'][] = $merchant;
                }
            }

            $merchant_list = $grouped_merchants;
        } else {
            $merchant_list = [];
        }

        return ['merchant_list' => $merchant_list];
    }

    /**
     *
     * @param array $data
     * @return array
     */
    private function generateCategoriesList($data)
    {
        list ('current_category' => $current_category,
            'current_merchant' => $current_merchant,
            'current_metal' => $current_metal,
            'current_merchant_network' => $current_merchant_network) = $data;

        // Фильтр категории в форме поиска
        if ($current_merchant) {
            // Отображаем категории магазина в которых есть не проданные товары
            $search_filter_categories = GoodCategory::findByRawSql([
                        "conditions" => "EXISTS(SELECT g.id "
                        . "FROM `good` g "
                        . "USE INDEX (`visible_goods`, `merchant`) "
                        . "WHERE g.merchant = ? "
                        . "AND g.category_id = gc.id "
                        . "AND g.deleted IS NULL "
                        . "AND g.hidden IS NULL "
                        . "AND g.sold IS NULL "
                        . "AND g.withdrawn IS NULL "
                        . "LIMIT 1)",
                        "params" => [$current_merchant->id],
            ]);
        } elseif ($current_merchant_network) {
            // Отображаем категории магазина в которых есть не проданные товары
            $search_filter_categories = GoodCategory::findByRawSql([
                        "conditions" => "EXISTS(SELECT g.id "
                        . "FROM `good` g "
                        . "USE INDEX (`visible_goods`, `merchant`) "
                        . "WHERE g.organization = ? "
                        . "AND g.category_id = gc.id "
                        . "AND g.deleted IS NULL "
                        . "AND g.hidden IS NULL "
                        . "AND g.sold IS NULL "
                        . "AND g.withdrawn IS NULL "
                        . "LIMIT 1)",
                        "params" => [$current_merchant_network->id],
            ]);
        } else {
            // Фильтр для поиска по категориям, когда вне товара/ломбарда(магазина)
            $search_filter_categories = GoodCategory::findSystemCategories();
        }

        // Фильтр категории->подкатегории в форме поиска
        $search_filter_subcategories = [];
        $sub_categories = [];
        foreach ($search_filter_categories as $search_filter_category) {
            if ($current_merchant) {
                $subcategories_filter_resultset = GoodSubCategory::findByRawSql([
                            "conditions" => "EXISTS(SELECT g.id "
                            . "FROM `good` g "
                            . "USE INDEX (`visible_goods`, `merchant`) "
                            . "WHERE g.merchant = ? "
                            . "AND gsc.parent_id = ? "
                            . "AND g.subcategory_id = gsc.id "
                            . "AND g.deleted IS NULL "
                            . "AND g.hidden IS NULL "
                            . "AND g.sold IS NULL "
                            . "AND g.withdrawn IS NULL "
                            . "LIMIT 1)",
                            "params" => [$current_merchant->id, $search_filter_category->id],
                ]);
            } else {
                $subcategories_filter_resultset = $search_filter_category->getSystemSubCategories();
            }

            $count_sub_cat = count($subcategories_filter_resultset);

            foreach ($subcategories_filter_resultset as $subcategory_filter) {
                if ($current_category && $search_filter_category->id == $current_category->id) {
                    if ($count_sub_cat > 12) {
                        $sub_categories[] = $subcategory_filter;
                    }
                }

                if ($count_sub_cat <= 12) {
                    $search_filter_subcategories[$search_filter_category->id][] = $subcategory_filter;
                }
            }
        }

        $metal_standarts = [];
        $search_filter_metals = [];

        // Если выбрана категория 'Ювелирные изделия' или её подкатегория
        if ($current_category && $current_category->id == GoodCategory::getIdJewelry()) {
            if ($current_merchant) {
                $gms_alias = GoodMetalStandart::class;

                $metal_standarts_data = GoodMetalStandart::query()
                        ->columns([
                            "gm.id as metal_id",
                            "gm.name as metal_name",
                            "{$gms_alias}.id as metal_standart_id",
                            "{$gms_alias}.name as metal_standart_name",
                        ])
                        ->innerJoin(Good::class, "g.metal_standart_id = {$gms_alias}.id", "g")
                        ->innerJoin(GoodMetal::class, "gm.id = g.metal_id", "gm")
                        ->andWhere("g.deleted IS NULL")
                        ->andWhere("g.hidden IS NULL")
                        ->andWhere("g.sold IS NULL")
                        ->andWhere("g.withdrawn IS NULL")
                        ->where("g.merchant = :merchant_id:", ["merchant_id" => $current_merchant->id])
                        ->groupBy("g.metal_id, {$gms_alias}.id")
                        ->orderBy("{$gms_alias}.name")
                        ->execute();
            } else {
                $gmsr_alias = GoodMetalStandartsRelations::class;

                $metal_standarts_data = GoodMetalStandartsRelations::query()
                        ->columns([
                            "gm.id as metal_id",
                            "gm.name as metal_name",
                            "gms.id as metal_standart_id",
                            "gms.name as metal_standart_name",
                        ])
                        ->innerJoin(GoodMetal::class, "gm.id = {$gmsr_alias}.metal_id", "gm")
                        ->innerJoin(GoodMetalStandart::class, "gms.id = {$gmsr_alias}.metal_standart_id", "gms")
                        ->andWhere("gm.system = 1 AND gms.system = 1")
                        ->orderBy("gms.name")
                        ->execute();
            }

            foreach ($metal_standarts_data as $item) {
                $search_filter_metals[$item->metal_id] = ['id' => $item->metal_id, 'name' => $item->metal_name];

                if ($current_metal) {
                    if ($item->metal_id == $current_metal->id) {
                        $metal_standarts[] = ['id' => $item->metal_standart_id, 'name' => $item->metal_standart_name];
                    }
                }
            }
        }

        return [
            'categories_list' => $search_filter_categories,
            'subcategories_list' => $search_filter_subcategories,
            'additional_subcategories_list' => $sub_categories,
            'metals_list' => $search_filter_metals,
            'metal_standarts_list' => $metal_standarts,
        ];
    }

    /**
     *
     * @param string $search_text
     * @return $this
     */
    public function searchText($search_text)
    {
        $this->search_text = $search_text;

        return $this;
    }

    /**
     *
     * @param bool $bool
     * @return $this
     */
    public function showCustomCategories($bool = true)
    {
        $this->show_custom_categories = (bool) $bool;

        return $this;
    }

    /**
     *
     * @param bool $bool
     * @return $this
     */
    public function showCustomSubCategories($bool = true)
    {
        $this->show_custom_subcategories = (bool) $bool;

        return $this;
    }

    /**
     *
     * @param bool $bool
     * @return $this
     */
    public function showWithoutSubCategories($bool = true)
    {
        $this->show_without_subcategories = (bool) $bool;

        return $this;
    }

    /**
     *
     * @param bool $bool
     * @return $this
     */
    public function showOnlyWithPrice($bool = true)
    {
        $this->show_only_with_price = (bool) $bool;

        return $this;
    }

    /**
     *
     * @param bool $bool
     * @return $this
     */
    public function showOnlyWithSize($bool = true)
    {
        $this->show_only_with_size = (bool) $bool;

        return $this;
    }

}
