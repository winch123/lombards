<?php

use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Version;
use Phalib\Paginator\Adapter\SimplePaginatorModel;
use Polombardam\StringsHelper;
use PolombardamModels\City;
use PolombardamModels\Good;
use PolombardamModels\GoodCategory;
use PolombardamModels\GoodSubCategory;
use PolombardamModels\Merchant;
use PolombardamModels\SearchForm;

class CityController extends ControllerBase {

    public function initialize() {
        $this->crumbs->add('home', '/', 'Главная');

        $this->view->yandexMaps = true;
    }

    public function rememberAction() {
        $this->view->disable();

        $city_id = $this->request->getPost('city_id');

        if ($city_id === 'detected') {
            if ($this->detected_city) {
                $this->setCookieSameSite('remember_city', $this->detected_city->id);

                $this->response->setJsonContent(['status' => true]);
            } else {
                $this->response->setJsonContent(['status' => false, 'message' => 'Город не был определен']);
            }
        } else {
            $city = City::findFirst((int) $city_id);

            if ($city) {
                $this->setCookieSameSite('remember_city', $city->id);

                $this->response->setJsonContent(['status' => true]);
            } else {
                $this->response->setJsonContent(['status' => false, 'message' => 'Такого города не существует']);
            }
        }

        return $this->response;
    }

    public function showAction() {
        $city_name_param = $this->dispatcher->getParam("cityName", 'string');
        $current_page = $this->request->getQuery('page', 'int'); // GET;+
        $current_category_name_param = $this->dispatcher->getParam("category", 'string');
        $current_subcategory_name_param = $this->dispatcher->getParam("subcategory", 'string');
        $current_merchant_id_param = $this->request->getQuery('merchant', 'int'); // GET;

        if ($city_name_param && StringsHelper::isRussian($city_name_param) || ($current_category_name_param && StringsHelper::isRussian($current_category_name_param)) || ($current_subcategory_name_param && StringsHelper::isRussian($current_subcategory_name_param))) {
            // редирект с русских названий на латиницу(транслит)
            $redirect_url = '/city/';

            $translited_city_name = StringsHelper::translitRusStringToUrl($city_name_param);
            $redirect_url .= $translited_city_name;

            if ($current_category_name_param) {
                $translited_category_name = StringsHelper::translitRusStringToUrl($current_category_name_param);
                $redirect_url .= '/' . $translited_category_name;

                if ($current_subcategory_name_param) {
                    $translited_subcategory_name = StringsHelper::translitRusStringToUrl($current_subcategory_name_param);
                    $redirect_url .= '/' . $translited_subcategory_name;
                }
            }

            $this->response->redirect($redirect_url, true, 301);

            return $this->response;
        }

        $city = City::findFirst([
                    'name = :name: OR name_translit = :name:',
                    'bind' => ['name' => $city_name_param,],
        ]);

        if ($city) {
            $this->setCurrentCity($city);
        } else {
            $response = new Response();
            $response->redirect("index/route404");
            return $response;
        }

        $city_title = ($city->name_case1 ? $city->name_case1 : ' города ' . $city->name);

        $this->crumbs->add('cityName', '/city/', 'Товары ' . $city_title, false);

        $search_form = new SearchForm($this);

        $g_alias = Good::class;

        $goods_criteria = Good::query()
                ->innerJoin(Merchant::class, "merchant = m.id", "m")
                ->innerJoin(GoodCategory::class, "{$g_alias}.category_id = cat.id", "cat")
                ->where("{$g_alias}.city = :city_name:", ["city_name" => $city->name])
                ->andWhere("{$g_alias}.deleted IS NULL")
                ->andWhere("{$g_alias}.hidden IS NULL")
                ->andWhere("{$g_alias}.sold IS NULL")
                ->andWhere("{$g_alias}.withdrawn IS NULL")
                ->andWhere("m.deleted is NULL")
                ->orderBy("{$g_alias}.bonus DESC, {$g_alias}.date DESC");

        if (!empty($current_category_name_param)) {
            // Фильтр по категории
            if ($current_category_name_param === 'Прочее' || $current_category_name_param === 'Prochee') {
                // виртуальная категория
                $goods_criteria->andWhere("cat.system = 0");

                $search_form->showCustomCategories();
            } else {
                $goods_criteria->andWhere("cat.name_translit = :category_name:", ['category_name' => $current_category_name_param]);

                if ($current_subcategory_name_param) {
                    if (trim($current_subcategory_name_param) == 'Без подкатегории' || trim($current_subcategory_name_param) == 'Bez-podkategorii') {
                        $goods_criteria->andWhere("{$g_alias}.subcategory_id IS NULL");

                        $search_form->showWithoutSubCategories();
                    } elseif (trim($current_subcategory_name_param) === 'Прочие подкатегории' || trim($current_subcategory_name_param) === 'Prochie-podcategorii') {
                        $goods_criteria->innerJoin(GoodSubCategory::class, "{$g_alias}.subcategory_id = subcat.id", "subcat");
                        $goods_criteria->andWhere("subcat.system = 0");

                        $search_form->showCustomSubCategories();
                    } else {
                        $goods_criteria->innerJoin(GoodSubCategory::class, "{$g_alias}.subcategory_id = subcat.id", "subcat");
                        $goods_criteria->andWhere("subcat.name_translit = :subcategory_name:", ["subcategory_name" => $current_subcategory_name_param]);
                    }
                }
            }
        } elseif (!empty($current_merchant_id_param)) {
            // Все категории
            $goods_criteria->andWhere("merchant = " . $current_merchant_id_param);
        }

        // need framework version at least v3.2.0
        $goods_query_builder = $goods_criteria->createBuilder();

        $paginator = new PaginatorQueryBuilder(array(
            'builder' => $goods_query_builder,
            'limit' => 48,
            'page' => $current_page,
        ));

        $gc_alias = GoodCategory::class;

        $categories = GoodCategory::query()
                ->columns([
                    "{$gc_alias}.id",
                    "{$gc_alias}.name",
                    "{$gc_alias}.name_translit",
                    "{$gc_alias}.system",
                    "COUNT(g.id) as rowcount"
                ])
                ->innerJoin(Good::class, "g.category_id = {$gc_alias}.id", "g")
                ->innerJoin(Merchant::class, "g.merchant = m.id", "m")
                ->where("{$gc_alias}.parent_id = 0")
                ->andWhere("g.deleted IS NULL")
                ->andWhere("g.hidden IS NULL")
                ->andWhere("g.sold IS NULL")
                ->andWhere("g.withdrawn IS NULL")
                ->andWhere("g.city = :city_name:", ["city_name" => $city->name])
                ->andWhere("m.deleted is NULL")
                ->groupBy("g.category_id")
                ->orderBy("{$gc_alias}.sort DESC, {$gc_alias}.name")
                ->execute();

        $categories_filter = [];
        $categories_id = [];
        $categories_count = [];
        $categories_all_count = 0;
        $categories_other_count = 0;

        foreach ($categories as $category) {
            $categories_all_count += $category->rowcount;

            if ($category->system) {
                $categories_filter[] = $category;
                $categories_id[] = $category->id;
                $categories_count[$category->id] = $category->rowcount;
            } else {
                $categories_other_count += (int) $category->rowcount;
            }
        }

        $merchants = Good::query()
                ->columns([
                    "m.name",
                    "m.id",
                    "m.shortlink",
                    "COUNT({$g_alias}.id) as rowcount"
                ])
                ->innerJoin(GoodCategory::class, "{$g_alias}.category_id = cat.id", "cat")
                ->innerJoin(Merchant::class, "{$g_alias}.merchant = m.id", "m")
                ->where("{$g_alias}.city = :name:", ["name" => $city->name])
                ->andWhere("cat.parent_id = 0")
                ->andWhere("{$g_alias}.deleted is NULL")
                ->andWhere("{$g_alias}.hidden is NULL")
                ->andWhere("{$g_alias}.sold is NULL")
                ->andWhere("{$g_alias}.withdrawn is NULL")
                ->andWhere("m.deleted is NULL")
                ->groupBy("{$g_alias}.merchant")
                ->orderBy("rowcount DESC, m.name ASC")
                ->execute();

        if ($current_merchant_id_param) {
            $this->view->merchantU = Merchant::findFirst((int) $current_merchant_id_param);
        }

        $merchants_all_count = 0;
        foreach ($merchants as $merchant) {
            $merchants_all_count += $merchant->rowcount;
        }

        /*
         * на странице объявлений города при выбранной категории (например ювелирка) сейчас заголовок просто "Самара",
         * а нужно в заголовок перенести то, что сейчас в тайтле. Будет дублироваться.
         * keywords="Ювелирные изделя в Самаре, объявления Самары, купить в ломбарде" (где ювелирные изделия - название выбранной категории).
         * description="Ювелирные изделия из ломбардов Самары" (где ювелирные изделия - выбранная категория)
         */

        $current_category = GoodCategory::findFirst([
                    '(name = :category_name: OR name_translit = :category_name:) AND parent_id = 0 AND system = 1',
                    'bind' => [
                        'category_name' => $current_category_name_param,
                    ],
        ]);

        $subcategories_data = GoodCategory::getMenuSubCategoriesData($categories_id, $city->name, $categories_count);

        if ($current_category) {
            $this->setCurrentCategory($current_category);

            if ($current_subcategory_name_param) {
                /*
                 * В теории подкатегория может встречаться в разных категориях.
                 * Если нет то имеет смысл сначала искать подкатегорию и у неё просто брать parent_id
                 */
                $current_sub_category = GoodSubCategory::findFirst([
                            '(name = :subcategory_name: OR name_translit = :subcategory_name:) AND parent_id = :parent_cat_id: AND system = 1',
                            'bind' => [
                                'subcategory_name' => $current_subcategory_name_param,
                                'parent_cat_id' => $current_category->id,
                            ],
                ]);

                if ($current_sub_category) {
                    $this->setCurrentSubCategory($current_sub_category);
                }
            }

            $query_subcategories_count = GoodCategory::query()
                    ->columns([
                        'COUNT(id) as rowcount'
                    ])
                    ->where('parent_id = :parent_id:', ["parent_id" => (int) $current_category->id])
                    ->execute();

            $subcategories_count_in_category = (int) $query_subcategories_count[0]->rowcount;

            $this->view->hasSubcategoriesInCategory = ($subcategories_count_in_category != 0 ? true : false);
        }

        if ($current_category) {
            $category_name_ru = $current_category->name;
        } elseif (empty($current_category_name_param)) {
            $category_name_ru = 'Все';
        } elseif ($current_category_name_param === 'Прочее' || $current_category_name_param === 'Prochee') {
            $category_name_ru = 'Прочее';
        } else {
            throw new Exception('Unknown category name');
        }

        if (empty($current_category_name_param)) {
            $keywords_starts_with = 'Товары из ломбардов';
            $description = 'Список товаров';
        } else {
            $keywords_starts_with = $category_name_ru;
            $description = $category_name_ru;
        }

        if ($city->name_case1 && $city->name_case2) {
            $this->meta->setKeywords($keywords_starts_with . " " . $city->name_case2 . ", объявления " . $city->name_case1 . ", купить в ломбарде");
            $description .= " из ломбардов " . $city->name_case1;
        } else if ($city->name_case1) {
            $this->meta->setKeywords($keywords_starts_with . " " . $city->name_case1 . ", объявления " . $city->name_case1 . ", купить в ломбарде");
            $description .= " из ломбардов " . $city->name_case1;
        } else {
            $this->meta->setKeywords($keywords_starts_with . " " . $city->name . ", объявления " . $city->name . ", купить в ломбарде");
            $description . " из ломбардов города " . $city->name;
        }

        $this->meta->setDescription($description . $this->getPageTextForDescription($paginator->paginate()));

        if ($current_sub_category) {
            $page_content_title = $current_sub_category->name . " из ломбардов ";
        } elseif ($current_category) {
            $page_content_title = $current_category->name . " из ломбардов ";
        } elseif ($category_name_ru === 'Прочее' || $category_name_ru === 'Prochee') {
            $page_content_title = "Прочие товары из ломбардов ";
        } else {
            $page_content_title = "Товары из ломбардов ";
        }

        $this->meta->setTitle($page_content_title  . $city_title . ", товары и цены" . $this->getPageTextForTitle($paginator->paginate()) );

        if (empty($current_category_name_param)) {
            $page_url_category_part = "";
        } else {
            $page_url_category_part = "/" . $current_category_name_param;

            if (!empty($current_subcategory_name_param)) {
                $page_url_category_part .= "/" . $current_subcategory_name_param;
            }
        }

        $this->view->cityTitle = $city_title;
        $this->view->tabLombardTitle = $page_content_title;
        $this->view->headerTitleSub = "Объявления о продаже товаров в ломбардах " . htmlspecialchars($city_title) . ", купить в ломбарде, цены на товары в ломбарде.";

        $this->view->merchants = $merchants;
        $this->view->pageMerchantUrl = $this->url->get('/city/' . $city->name_translit . '?merchant=');
        $this->view->merchantsAllCount = $merchants_all_count;

        $this->view->categoriesAllCount = $categories_all_count;
        $this->view->categoriesOtherCount = $categories_other_count;
        $this->view->categories = $categories_filter;
        $this->view->categoryName = $category_name_ru;
        $this->view->subcategoryName = $current_subcategory_name_param;

        // Подкатегории
        $this->view->subcategoriesData = $subcategories_data;

        $this->makeSearchForm($search_form);

        $this->view->page = $paginator->paginate();
        $this->view->pageUrlClear = $this->url->get('/good/');
        $this->view->pageUrl = $this->url->get('/city/' . $city->name_translit . $page_url_category_part);
    }

}
