<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Version;
use Phalib\Paginator\Adapter\SimplePaginatorModel;
use PolombardamModels\City;
use PolombardamModels\Country;
use PolombardamModels\Good;
use PolombardamModels\GoodCategory;
use PolombardamModels\GoodMetal;
use PolombardamModels\GoodMetalStandart;
use PolombardamModels\GoodSubCategory;
use PolombardamModels\Merchant;
use PolombardamModels\Organization;
use PolombardamModels\SearchForm;

class SearchController extends ControllerBase {

    public function indexAction() {
        $this->crumbs->add('home', '/', 'Главная');
        $this->crumbs->add('search', '', 'Поиск', false);

        $param_search_text = $this->request->getQuery('text');
        $param_city_name = $this->getCityNameParam();
        $param_country_code = $this->getCountryCodeParam();

        // Допустимые значения all, /[\d]*/
        $param_phone_model_id = $this->request->getQuery('phone_model_id', 'string');
        $param_merchant_id = $this->request->getQuery('merchant', 'int');
        $is_merchant_network = (stripos($this->request->getQuery('merchant', 'string'), 'network') !== false);

        if ($is_merchant_network) {
            $this->setCurrentMerchantNetwork(Organization::findFirst((int) $param_merchant_id));
        }

        $param_min_price_filter = $this->request->getQuery('min_price', 'int');
        $param_max_price_filter = $this->request->getQuery('max_price', 'int');
        $param_only_with_price = $this->request->getQuery('zero_price', 'int');

        // Если параметр не задан в строке адреса значит он установлен по умолчанию
        $param_only_with_price = (is_null($param_only_with_price) ? 1 : $param_only_with_price);

        $param_sort_order = $this->request->getQuery('sorter', 'string', 'quality');
        $param_metal_id = $this->request->getQuery('metal', 'string');
        $param_metal_standart_id = $this->request->getQuery('metal_standart', 'string');

        $current_page_number = $this->request->getQuery('page', 'int');

        $g_alias = Good::class;

        // Search result start
        $goods_criteria = Good::query()
                ->innerJoin(Merchant::class, "{$g_alias}.merchant=mer.id", "mer")
                ->innerJoin(GoodCategory::class, "{$g_alias}.category_id = cat.id", "cat")
                ->leftJoin(GoodSubCategory::class, "{$g_alias}.subcategory_id = subcat.id", "subcat")
                ->where("{$g_alias}.deleted IS NULL")
                ->andWhere("{$g_alias}.hidden IS NULL")
                ->andWhere("{$g_alias}.sold IS NULL")
                ->andWhere("{$g_alias}.withdrawn IS NULL")
                // hide goods from deleted merchants if any
                ->andWhere("mer.deleted IS NULL");

        if ($param_sort_order === 'quality') {
            $goods_criteria->orderBy("{$g_alias}.bonus DESC, {$g_alias}.date DESC");
        } elseif ($param_sort_order === 'date') {
            $goods_criteria->orderBy("{$g_alias}.date DESC, {$g_alias}.bonus DESC");
        } elseif ($param_sort_order === 'cheaper') {
            $goods_criteria->orderBy("{$g_alias}.price ASC");
        } elseif ($param_sort_order === 'expensive') {
            $goods_criteria->orderBy("{$g_alias}.price DESC");
        }

        $search_form = new SearchForm($this);

        if (!empty($param_search_text)) {
            /*
             * Пробел необходим чтобы искались слова только с начала слова
             * (т.е. для велосипед будет находится только по: "вело", "велоси", но не по "лоси", "сипед" и т.д.).
             *
             * Видимо сделано не через полнотекстовый поиск как временное решение.
             */
            $goods_criteria->andWhere("CONCAT(' ', {$g_alias}.name) LIKE :good_name:", ['good_name' => '% ' . $param_search_text . '%']);

            $search_form->searchText($param_search_text);
        }

        if (!empty($param_city_name)) {
            $country = null;

            if ($param_city_name !== 'all') {
                $city = City::findFirst([
                            'name = :city_name: OR name_translit = :city_name:',
                            'bind' => ['city_name' => $param_city_name]
                ]);

                if ($city) {
                    $this->setCurrentCity($city);
                    $goods_criteria->andWhere("{$g_alias}.city = :city_name:", ['city_name' => $city->name]);
                }
            } elseif ($param_country_code) {
                $country = Country::findFirst([
                            "code = :code:",
                            "bind" => ['code' => $param_country_code],
                ]);

                if ($country) {
                    $this->setCurrentCountry($country);
                    $goods_criteria->innerJoin(City::class, "{$g_alias}.city = ct.name", "ct");
                    $goods_criteria->andWhere("ct.country_id = :country_id:", ["country_id" => (int) $country->id]);
                }

                $this->view->is_country = true;
            }
        }

        // Допустимые значения all, other, /[\d]*/
        $param_category_id = $this->request->getQuery('category_id', 'string');

        if ($param_phone_model_id && is_numeric($param_phone_model_id)) {
            // another hack for mobile category >:(
            $param_category_id = $param_phone_model_id;
        }

        if (!empty($param_category_id) && $param_category_id != 'all') {
            if ($param_category_id == 'other') {
                $goods_criteria->andWhere("cat.system = 0");
                $search_form->showCustomCategories();
            } else {
                $category = GoodCategory::findFirst((int) $param_category_id);

                if ($category && !$category->isSubCategory()) {
                    $goods_criteria->andWhere("{$g_alias}.category_id = :category_id:", ['category_id' => $param_category_id]);

                    $this->setCurrentCategory($category);
                } elseif ($category && $category->isSubCategory()) {
                    $subcategory = GoodSubCategory::findFirst((int) $category->id);

                    $goods_criteria->andWhere("{$g_alias}.subcategory_id = :subcategory_id:", ['subcategory_id' => $subcategory->id]);

                    $this->setCurrentSubCategory($subcategory);
                }
            }
        }

        if ($this->current_merchant_network) {
            $goods_criteria->andWhere("{$g_alias}.organization = :merchant_network_id:", ['merchant_network_id' => (int) $this->current_merchant_network->id]);
        } else {
            if (!empty($param_merchant_id)) {
                $merchant = Merchant::findFirst((int) $param_merchant_id);

                if ($merchant) {
                    $this->setCurrentMerchant($merchant);
                }

                $goods_criteria->andWhere("{$g_alias}.merchant = :merchant_id:", ['merchant_id' => $param_merchant_id]);
            }
        }

        $is_jewelry_subcategory = ($category && $category->parent_id == GoodCategory::getIdJewelry());

        $param_min_size_filter = ($is_jewelry_subcategory ? $this->request->getQuery('min_size', 'float') : null);
        $param_max_size_filter = ($is_jewelry_subcategory ? $this->request->getQuery('max_size', 'float') : null);
        $param_only_with_size = ($is_jewelry_subcategory ? $this->request->getQuery('zero_size', 'int') : null);

        if (!empty($param_min_price_filter)) {
            if (empty($param_only_with_price)) {
                $goods_criteria->andWhere("({$g_alias}.price >= :min_price: OR {$g_alias}.price = 0)", ['min_price' => $param_min_price_filter]);
            } else {
                $goods_criteria->andWhere("{$g_alias}.price >= :min_price:", ['min_price' => $param_min_price_filter]);
            }

            $this->view->filterPriceMin = $param_min_price_filter;
        }

        if (!empty($param_max_price_filter)) {
            $goods_criteria->andWhere("{$g_alias}.price <= :max_price:", ['max_price' => $param_max_price_filter]);
            $this->view->filterPriceMax = $param_max_price_filter;
        }

        if (empty($param_only_with_price)) {
            $search_form->showOnlyWithPrice(false);
        } else {
            $search_form->showOnlyWithPrice();
            $goods_criteria->andWhere("{$g_alias}.price > 0");
        }

        if (!empty($param_min_size_filter)) {
            if (empty($param_only_with_size)) {
                $goods_criteria->andWhere("({$g_alias}.size >= :min_size_filter: OR {$g_alias}.size IS NULL)", ["min_size_filter" => $param_min_size_filter]);
            } else {
                $goods_criteria->andWhere("{$g_alias}.size >= :min_size_filter:", ["min_size_filter" => $param_min_size_filter]);
            }

            $this->current_min_size = $param_min_size_filter;
            $this->view->filterSizeMin = $param_min_size_filter;
        }

        if (!empty($param_max_size_filter)) {
            if (empty($param_only_with_size)) {
                $goods_criteria->andWhere("({$g_alias}.size <= :max_size_filter: OR {$g_alias}.size IS NULL)", ["max_size_filter" => $param_max_size_filter]);
            } else {
                $goods_criteria->andWhere("{$g_alias}.size <= :max_size_filter:", ["max_size_filter" => $param_max_size_filter]);
            }

            $this->current_max_size = $param_max_size_filter;
            $this->view->filterSizeMax = $param_max_size_filter;
        }

        if (empty($param_only_with_size)) {
            $search_form->showOnlyWithSize(false);
        } else {
            $search_form->showOnlyWithSize();
            $goods_criteria->andWhere("{$g_alias}.size IS NOT NULL");
        }

        if (!empty($param_metal_id) && $param_metal_id != 'all' && $this->current_category && $this->current_category->name === 'Ювелирные изделия') {
            $metal = GoodMetal::findFirst((int) $param_metal_id);

            $goods_criteria->andWhere("{$g_alias}.metal_id = :metal_id:", ["metal_id" => (int) $param_metal_id]);

            $this->setCurrentMetal($metal);

            if (!empty($param_metal_standart_id) && $param_metal_standart_id != 'all') {
                $metal_standart = GoodMetalStandart::findFirst((int) $param_metal_standart_id);

                $this->setCurrentMetalStandart($metal_standart);

                $goods_criteria->andWhere("{$g_alias}.metal_standart_id = :metal_standart_id:", ["metal_standart_id" => (int) $param_metal_standart_id]);
            }
        }

        // need framework version at least v3.2.0
        $goods_query_builder = $goods_criteria->createBuilder();

        $paginator = new PaginatorQueryBuilder(array(
            'builder' => $goods_query_builder,
            'limit' => 48,
            'page' => $current_page_number
        ));

        // Search result end

        if ($this->current_city) {
            $filter_city_name = $this->current_city->name_translit;
        } elseif ($this->current_country) {
            $filter_city_name = 'all_country_' . $country->code;
        } else {
            $filter_city_name = $param_city_name;
        }

        $page_query_params = [
            'text' => $param_search_text,
            'city_name' => $filter_city_name,
            'category_id' => $param_category_id,
            'merchant_id' => ($this->current_merchant_network ? $this->request->getQuery('merchant', 'string') : $param_merchant_id),
            'min_price' => $param_min_price_filter,
            'max_price' => $param_max_price_filter,
            'zero_price' => $param_only_with_price,
            'min_size' => $param_min_size_filter,
            'max_size' => $param_max_size_filter,
            'zero_size' => $param_only_with_size,
            'sort_order' => $param_sort_order,
            'metal_id' => $param_metal_id,
            'metal_standart_id' => $param_metal_standart_id,
        ];

        if (!$this->current_category || !$this->current_subcategory) {
            $this->view->goods_filter = $this->makeCategoriesFilter($goods_criteria, $page_query_params);
        } elseif ($this->current_category && $this->current_subcategory && $this->current_category->name === 'Ювелирные изделия') {
            if (!$this->current_metal) {
                $this->view->goods_filter = $this->makeMetalsFilter($goods_criteria, $page_query_params);
            } elseif (!$this->current_metal_standart) {
                $this->view->goods_filter = $this->makeMetalsStandartFilter($goods_criteria, $page_query_params);
            }
        }

        $this->meta->setTitle($this->generatePageTitle($param_search_text) . $this->getPageTextForTitle($paginator->paginate()));
        $this->meta->setDescription($this->generateMetaDescription($param_search_text) . $this->getPageTextForDescription($paginator->paginate()));
        $this->meta->setKeywords($this->generateMetaKeywords($param_search_text));

        $this->view->page = $paginator->paginate();
        $this->view->pageUrlClear = $this->url->get('/good/');
        $this->view->pageUrl = $this->url->get(
                '/search?text=' . urlencode($param_search_text) .
                '&city=' . ($city ? $city->name_translit : '') .
                '&category_id=' . $param_category_id .
                '&merchant=' . ($this->current_merchant_network ? $this->request->getQuery('merchant', 'string') : $param_merchant_id) .
                '&min_price=' . $param_min_price_filter .
                '&max_price=' . $param_max_price_filter .
                '&zero_price=' . $param_only_with_price .
                ($param_min_size_filter ? '&min_size=' . $param_min_size_filter : '') .
                ($param_max_size_filter ? '&max_size=' . $param_max_size_filter : '') .
                ($param_only_with_size ? '&zero_size=' . $param_only_with_size : '') .
                '&sorter=' . $param_sort_order .
                '&metal=' . $param_metal_id .
                '&metal_standart=' . $param_metal_standart_id
        );

        $this->view->field_sorter = $param_sort_order;
        $this->view->filter_breadcrumbs = $this->makeFilterBreadcrumbs([
            'zero_price' => $param_only_with_price,
            'zero_size' => $param_only_with_size,
        ]);

        $this->makeSearchForm($search_form);
    }

    /**
     *
     * @param string|null $param_search_text
     * @return string
     */
    private function generatePageTitle(?string $param_search_text): string
    {
        $elements = [];

        if ($this->current_subcategory) {
            $elements[] = $this->current_subcategory->getName();
        } elseif ($this->current_category) {
            $elements[] = $this->current_category->getName();
        } elseif (empty($param_search_text)) {
            $elements[] = 'Все товары';
        }

        if ($param_search_text) {
            $elements[] = $param_search_text;
        }

        if ($this->current_merchant) {
            $elements[] = 'в ломбарде';
        } else {
            $elements[] = 'в ломбардах';
        }

        if ($this->current_merchant) {
            $elements[] = $this->current_merchant->getName();
        } elseif ($this->current_merchant_network) {
            $elements[] = $this->current_merchant_network->getName();
        } elseif ($this->current_city) {
            $elements[] = $this->current_city->getNameCase1();
        } elseif ($this->current_country) {
            $elements[] = $this->current_country->getNameCase1();
        }

        return implode(' ', $elements);
    }

    /**
     * $название_города, товары из ломбарда, $категория, $подкатегория, $поисковый_запрос, $название_ломбарда
     *
     * @param string|null $param_search_text
     * @return string
     */
    private function generateMetaKeywords(?string $param_search_text): string
    {
        $elements = [];

        if ($this->current_city) {
            $elements[] = $this->current_city->getName();
        }

        $elements[] = 'товары из ломбарда';

        if ($this->current_category) {
            $elements[] = $this->current_category->getName();
        }

        if ($this->current_subcategory) {
            $elements[] = $this->current_subcategory->getName();
        }

        if (empty($param_search_text)) {
            $elements[] = 'Все товары';
        }

        if ($param_search_text) {
            $elements[] = $param_search_text;
        }

        if ($this->current_merchant) {
            $elements[] = $this->current_merchant->getName();
        }

        return implode(', ', $elements);
    }

    /**
     * $Название_города: товары из ломбардов в категории $категория, $подкатегория по запросу $поисковый запрос
     *
     * @param string|null $param_search_text
     * @return string
     */
    private function generateMetaDescription(?string $param_search_text): string
    {
        $elements = [];

        if ($this->current_city) {
            $elements[] = $this->current_city->getName() . ':';
        }

        $elements[] = 'товары из ломбардов';

        if ($this->current_category || $this->current_subcategory) {
            $elements[] = 'в категории';

            if ($this->current_category) {
                $elements[] = $this->current_category->getName() . ',';
            }

            if ($this->current_subcategory) {
                $elements[] = $this->current_subcategory->getName();
            }
        }

        if ($param_search_text) {
            $elements[] = 'по запросу';
            $elements[] = $param_search_text;
        }

        return implode(' ', $elements);
    }

    /**
     *
     * @param array $url_params
     * @return array
     */
    private function makeFilterBreadcrumbs($url_params = []) {
        $country = $this->current_country;
        $city = $this->current_city;
        $merchant = $this->current_merchant;
        $category = $this->current_category;
        $subcategory = $this->current_subcategory;
        $min_size = $this->current_min_size;
        $max_size = $this->current_max_size;
        $metal = $this->current_metal;
        $metal_standart = $this->current_metal_standart;
        $current_merchant_network = $this->current_merchant_network;

        $query_category_id = $this->request->getQuery('category_id', 'string');

        $bread_crumbs = [];

        if ($city) {
            $url_params['city'] = $city->name_translit;

            $alt_city_name = ($city->name_case1 ?: $city->name);

            if ($merchant || $category || $query_category_id === 'other') {
                $bread_crumbs[] = $this->buildFilterBreadcrumbLink('Все товары ' . $alt_city_name, $url_params);
            } else {
                $bread_crumbs[] = $this->buildFilterBreadcrumbLink('Все товары ' . $alt_city_name);
            }

            if ($merchant) {
                $url_params['merchant'] = $merchant->id;

                if ($category) {
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($merchant->name, $url_params);
                } else {
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($merchant->name);
                }
            }
        } elseif ($country) {
            $url_params['city'] = 'all_country_' . $country->code;
            $bread_crumbs[] = $this->buildFilterBreadcrumbLink('Все товары ' . $country->name_case1, $url_params);
        } else {
            $url_params['city'] = 'all';
            $bread_crumbs[] = $this->buildFilterBreadcrumbLink('Все товары', $url_params);
        }

        if ($current_merchant_network && !$category) {
            $bread_crumbs[] = $this->buildFilterBreadcrumbLink('Все ломбарды сети ' . $current_merchant_network->merchant_name);
        }

        if ($category) {
            if ($current_merchant_network) {
                $url_params['city'] = $city->name;
                $url_params['merchant'] = 'network' . $current_merchant_network->id;
                $bread_crumbs[] = $this->buildFilterBreadcrumbLink('Все ломбарды сети ' . $current_merchant_network->merchant_name, $url_params);
            }

            $url_params['category_id'] = $category->id;

            if ($subcategory) {
                $bread_crumbs[] = $this->buildFilterBreadcrumbLink($category->name, $url_params);
                $url_params['category_id'] = $subcategory->id;

                $show_size_breadcrambs = ($subcategory->parent_id == GoodCategory::getIdJewelry() && ($min_size || $max_size));

                if ($metal || $show_size_breadcrambs) {
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($subcategory->name, $url_params);
                } else {
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($subcategory->name);
                }

                if ($show_size_breadcrambs) {
                    $size_link_name = 'Размер';

                    if ($min_size) {
                        $url_params['min_size'] = $min_size;
                        $size_link_name .= ' от ' . $min_size;
                    }

                    if ($max_size) {
                        $url_params['max_size'] = $max_size;
                        $size_link_name .= ' до ' . $max_size;
                    }

                    if ($metal) {
                        $bread_crumbs[] = $this->buildFilterBreadcrumbLink($size_link_name, $url_params);
                    } else {
                        $bread_crumbs[] = $this->buildFilterBreadcrumbLink($size_link_name);
                    }
                }
            } else {
                if ($metal) {
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($category->name, $url_params);
                } else {
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($category->name);
                }
            }

            if ($metal) {
                $url_params['metal'] = $metal->id;

                if ($metal_standart) {
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($metal->name, $url_params);
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($metal_standart->name);
                } else {
                    $bread_crumbs[] = $this->buildFilterBreadcrumbLink($metal->name);
                }
            }
        } else {
            if ($query_category_id === 'other') {
                $bread_crumbs[] = $this->buildFilterBreadcrumbLink('Прочее');
            } else {
                $bread_crumbs[] = $this->buildFilterBreadcrumbLink('Все категории');
            }
        }

        return $bread_crumbs;
    }

    /**
     *
     * @param string $text
     * @param array $url_params
     * @return array
     */
    private function buildFilterBreadcrumbLink($text, $url_params = null) {
        if ($url_params) {
            return ['text' => $text, 'url' => http_build_query($url_params)];
        } else {
            return ['text' => $text];
        }
    }

    /**
     *
     * @param Criteria $goods_criteria
     * @param array $query_params
     * @return array
     */
    private function makeCategoriesFilter($goods_criteria, $query_params = []) {
        $goods_category_criteria = clone $goods_criteria;

        $g_alias = Good::class;

        if (!$this->current_category) {
            $goods_category_result = $goods_category_criteria->columns([
                        "cat.id",
                        "cat.name",
                        "cat.system",
                        "COUNT({$g_alias}.id) as goods_count"
                    ])
                    ->groupBy("{$g_alias}.category_id")
                    ->orderBy("cat.sort DESC, cat.name")
                    ->execute();
        } elseif (!$this->current_subcategory) {
            $goods_category_result = $goods_category_criteria->columns([
                        "subcat.id",
                        "subcat.name",
                        "subcat.system",
                        "COUNT({$g_alias}.id) as goods_count"
                    ])
                    ->groupBy("{$g_alias}.subcategory_id")
                    ->orderBy("subcat.sort DESC, subcat.name")
                    ->andWhere("subcat.id IS NOT NULL")
                    ->execute();
        } else {
            $goods_category_result = [];
        }

        $goods_others_category_count = 0;
        $goods_category = [];
        foreach ($goods_category_result as $good_category_result) {
            if ($this->current_merchant || $good_category_result->system) {
                $goods_category[] = $good_category_result;
            } elseif (!$this->current_merchant && !$this->current_category) {
                $goods_others_category_count += $good_category_result->goods_count;
            }
        }

        if ($goods_others_category_count > 0) {
            $std_object = new stdClass();
            $std_object->id = 'other';
            $std_object->name = 'Прочее';
            $std_object->goods_count = $goods_others_category_count;

            $goods_category[] = $std_object;
        }

        $goods_filter = [];
        foreach ($goods_category as $good_category) {
            $good_category->url = $this->url->get(
                    '/search?text=' . urlencode($query_params['text']) .
                    '&city=' . $query_params['city_name'] .
                    '&category_id=' . $good_category->id .
                    '&merchant=' . $query_params['merchant_id'] .
                    '&min_price=' . $query_params['min_price'] .
                    '&max_price=' . $query_params['max_price'] .
                    '&zero_price=' . $query_params['zero_price'] .
                    ($query_params['min_size'] ? '&min_size=' . $query_params['min_size'] : '') .
                    ($query_params['max_size'] ? '&max_size=' . $query_params['max_size'] : '') .
                    ($query_params['zero_size'] ? '&zero_size=' . $query_params['zero_size'] : '') .
                    '&sorter=' . $query_params['sort_order'] .
                    '&metal=' . $query_params['metal_id'] .
                    '&metal_standart=' . $query_params['metal_standart_id'] .
                    '&page='
            );

            $goods_filter[] = $good_category;
        }

        return (count($goods_filter) > 1 ? $goods_filter : []);
    }

    /**
     * Фильтр для металлов ювелирных изделий
     *
     * @param Criteria $goods_criteria
     * @param array $query_params
     * @return array
     */
    private function makeMetalsFilter($goods_criteria, $query_params = []) {
        if (!$this->current_category || !$this->current_subcategory || $this->current_category->name !== 'Ювелирные изделия' || $this->current_metal) {
            // список отображаем только если выбрана подкатегория ювелирных изделий и не выбраны металлы/пробы
            return [];
        }

        $goods_metals_criteria = clone $goods_criteria;

        $g_alias = Good::class;

        $goods_metals_result = $goods_metals_criteria->columns([
                    "metal.id",
                    "metal.name",
                    "metal.system",
                    "COUNT({$g_alias}.id) as goods_count"
                ])
                ->innerJoin(GoodMetal::class, "{$g_alias}.metal_id = metal.id", "metal")
                ->groupBy("{$g_alias}.metal_id")
                ->orderBy("metal.name")
                ->execute();

        $goods_metals = [];
        foreach ($goods_metals_result as $good_metal_result) {
            if ($this->current_merchant || $good_metal_result->system) {
                $goods_metals[] = $good_metal_result;
            }
        }

        $goods_filter = [];
        foreach ($goods_metals as $good_metal) {
            $good_metal->url = $this->url->get(
                    '/search?text=' . urlencode($query_params['text']) .
                    '&city=' . $query_params['city_name'] .
                    '&category_id=' . $query_params['category_id'] .
                    '&merchant=' . $query_params['merchant_id'] .
                    '&min_price=' . $query_params['min_price'] .
                    '&max_price=' . $query_params['max_price'] .
                    '&zero_price=' . $query_params['zero_price'] .
                    ($query_params['min_size'] ? '&min_size=' . $query_params['min_size'] : '') .
                    ($query_params['max_size'] ? '&max_size=' . $query_params['max_size'] : '') .
                    ($query_params['zero_size'] ? '&zero_size=' . $query_params['zero_size'] : '') .
                    '&sorter=' . $query_params['sort_order'] .
                    '&metal=' . $good_metal->id .
                    '&metal_standart=' . $query_params['metal_standart_id'] .
                    '&page='
            );

            $goods_filter[] = $good_metal;
        }

        return (count($goods_filter) > 1 ? $goods_filter : []);
    }

    /**
     * Фильтр для проб металлов ювелирных изделий
     *
     * @param Criteria $goods_criteria
     * @param array $query_params
     * @return array
     */
    private function makeMetalsStandartFilter($goods_criteria, $query_params = []) {
        if (!$this->current_category || !$this->current_subcategory || $this->current_category->name !== 'Ювелирные изделия' || $this->current_metal_standart) {
            // список отображаем только если выбрана подкатегория ювелирных изделий и не выбраны металлы/пробы
            return [];
        }

        $goods_metals_criteria = clone $goods_criteria;

        $g_alias = Good::class;

        $goods_metals_result = $goods_metals_criteria->columns([
                    "m_standart.id",
                    "m_standart.name",
                    "m_standart.system",
                    "COUNT({$g_alias}.id) as goods_count"
                ])
                ->innerJoin(GoodMetal::class, "{$g_alias}.metal_id = metal.id", "metal")
                ->innerJoin(GoodMetalStandart::class, "{$g_alias}.metal_standart_id = m_standart.id", "m_standart")
                ->groupBy("{$g_alias}.metal_standart_id")
                ->orderBy("m_standart.name")
                ->execute();

        $goods_metals_standart = [];
        foreach ($goods_metals_result as $good_metal_standart_result) {
            if ($this->current_merchant || $good_metal_standart_result->system) {
                $goods_metals_standart[] = $good_metal_standart_result;
            }
        }

        $goods_filter = [];
        foreach ($goods_metals_standart as $good_metal_standart) {
            $good_metal_standart->url = $this->url->get(
                    '/search?text=' . urlencode($query_params['text']) .
                    '&city=' . $query_params['city_name'] .
                    '&category_id=' . $query_params['category_id'] .
                    '&merchant=' . $query_params['merchant_id'] .
                    '&min_price=' . $query_params['min_price'] .
                    '&max_price=' . $query_params['max_price'] .
                    '&zero_price=' . $query_params['zero_price'] .
                    ($query_params['min_size'] ? '&min_size=' . $query_params['min_size'] : '') .
                    ($query_params['max_size'] ? '&max_size=' . $query_params['max_size'] : '') .
                    ($query_params['zero_size'] ? '&zero_size=' . $query_params['zero_size'] : '') .
                    '&sorter=' . $query_params['sort_order'] .
                    '&metal=' . $query_params['metal_id'] .
                    '&metal_standart=' . $good_metal_standart->id .
                    '&page='
            );

            $goods_filter[] = $good_metal_standart;
        }

        return (count($goods_filter) > 1 ? $goods_filter : []);
    }

}
