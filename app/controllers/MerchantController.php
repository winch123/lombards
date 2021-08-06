<?php

use Phalcon\Filter;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalib\Helpers\OpenGraph;
use Phalib\Paginator\Adapter\SimplePaginatorModel;
use Polombardam\StringsHelper;
use PolombardamModels\City;
use PolombardamModels\Good;
use PolombardamModels\GoodCategory;
use PolombardamModels\GoodSubCategory;
use PolombardamModels\Merchant;
use PolombardamModels\MerchantTag;
use PolombardamModels\Organization;
use PolombardamModels\SearchForm;
use PolombardamModels\Tag;

class MerchantController extends ControllerBase {

    public function initialize() {
        $this->crumbs->add('home', '/', 'Главная');

        $this->view->yandexMaps = true;
    }

    public function indexAction() {
        $current_page_number = $this->request->getQuery('page', 'int'); // GET;

        $filter = new Filter();
        $shortlink_string = $filter->sanitize(implode('/', $this->dispatcher->getParams()), 'string');
        $merchant_id = $this->dispatcher->getParam("merchantId", 'int');

        if ($merchant_id && is_numeric($merchant_id)) {
            $merchant = Merchant::findFirst((int) $merchant_id);
        } else {
            $merchant = Merchant::findFirst([
                        "shortlink = :shortlink:",
                        "bind" => ['shortlink' => $shortlink_string],
            ]);
        }

        if (!$merchant) {
            // возможно короткая ссылка не на ломбард, а на сеть ломбардов
            $organization = Organization::findFirst([
                        "shortlink = :shortlink:",
                        "bind" => ['shortlink' => $shortlink_string],
            ]);

            if ($organization) {
                $this->dispatcher->forward([
                    "controller" => "merchantnetwork"
                ]);
            }
        }

        if (!$merchant || $merchant->deleted) {
            $response = new Response();
            $response->redirect("index/route404");
            return $response;
        } else {
            $this->setCurrentMerchant($merchant);

            $current_category_name_param = $this->dispatcher->getParam("category", 'string');
            $current_subcategory_name_param = $this->dispatcher->getParam("subcategory", 'string');

            if (($current_category_name_param && StringsHelper::isRussian($current_category_name_param)) || ($current_subcategory_name_param && StringsHelper::isRussian($current_subcategory_name_param))) {
                $redirect_url = '/merchant/' . (int) $merchant->id;

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

            $current_category = GoodCategory::findFirst([
                        '(name = :category_name: OR name_translit = :category_name:) AND parent_id = 0',
                        'bind' => [
                            'category_name' => $current_category_name_param,
                        ],
            ]);

            if ($current_category) {
                $this->setCurrentCategory($current_category);

                $check_merchant_has_product_with_subcategory = Good::findFirst(
                                "merchant = " . (int) $merchant->id . " AND " .
                                "category_id = " . (int) $current_category->id . " AND " .
                                "subcategory_id IS NOT NULL" . " AND " .
                                "deleted IS NULL" . " AND " .
                                "hidden IS NULL" . " AND " .
                                "sold IS NULL" . " AND " .
                                "withdrawn IS NULL");

                $has_product_with_subcategory = ($check_merchant_has_product_with_subcategory ? true : false);
                $this->view->hasProductWithSubcategory = $has_product_with_subcategory;
            }

            if ($current_category && $current_subcategory_name_param) {
                /*
                 * В теории подкатегория может встречаться в разных категориях.
                 * Если нет то имеет смысл сначала искать подкатегорию и у неё просто брать parent_id
                 */
                $current_sub_category = GoodSubCategory::findFirst([
                            '(name = :subcategory_name: OR name_translit = :subcategory_name:) AND parent_id = :parent_cat_id:',
                            'bind' => [
                                'subcategory_name' => $current_subcategory_name_param,
                                'parent_cat_id' => $current_category->id,
                            ],
                ]);

                if ($current_sub_category) {
                    $this->setCurrentSubCategory($current_sub_category);
                }
            }

            $search_form = new SearchForm($this);

            $goods_criteria = Good::query()
                    ->where('merchant = :merchant_id:', ['merchant_id' => $merchant->id])
                    ->andWhere('deleted IS NULL')
                    ->andWhere('hidden IS NULL')
                    ->andWhere('sold IS NULL')
                    ->andWhere('withdrawn IS NULL')
                    ->orderBy('bonus DESC, date DESC');

            if ($current_category) {
                $goods_criteria->andWhere('category_id = :category_id:', ['category_id' => $current_category->id]);

                if ($current_sub_category) {
                    $goods_criteria->andWhere('subcategory_id = :subcategory_id:', ['subcategory_id' => $current_sub_category->id]);
                } elseif (trim($current_subcategory_name_param) === 'Без подкатегории' || trim($current_subcategory_name_param) === 'Bez-podkategorii') {
                    $goods_criteria->andWhere('subcategory_id IS NULL');

                    $search_form->showWithoutSubCategories();
                }
            }

            $gc_alias = GoodCategory::class;

            $categories_filter = GoodCategory::query()
                    ->columns([
                        "{$gc_alias}.id",
                        "{$gc_alias}.name",
                        "{$gc_alias}.name_translit",
                        "COUNT(g.id) as rowcount"
                    ])
                    ->innerJoin(Good::class, "g.category_id = {$gc_alias}.id", "g")
                    ->where("g.merchant = :merchant_id:", ["merchant_id" => $merchant->id])
                    ->andWhere("g.deleted IS NULL")
                    ->andWhere("g.hidden IS NULL")
                    ->andWhere("g.sold IS NULL")
                    ->andWhere("g.withdrawn IS NULL")
                    ->andWhere("{$gc_alias}.parent_id = 0")
                    ->groupBy("{$gc_alias}.id")
                    ->orderBy("{$gc_alias}.sort DESC, {$gc_alias}.name")
                    ->execute();

            $categories_id = [];
            $categories_count = [];

            $categories_filter_all_count = 0;
            foreach ($categories_filter as $category_filter) {
                $categories_filter_all_count += $category_filter->rowcount;
                $categories_id[] = $category_filter->id;
                $categories_count[$category_filter->id] = $category_filter->rowcount;
            }

            $subcategories_data = GoodCategory::getMerchantMenuSubCategoriesData($categories_id, $merchant->id, $categories_count);

            $this->view->categories = $categories_filter;
            $this->view->categories_filter_all_count = $categories_filter_all_count;

            $is_merchant_in_network = false;
            if (!$merchant->custom) {
                $is_merchant_in_network = $merchant->isBelongsToNetwork();

                if ($is_merchant_in_network) {
                    $network_url = $merchant->getNetworkUrl();
                }
            }

            $this->view->network_url = $network_url;
            $this->view->isInNetwork = $is_merchant_in_network;

            // need framework version at least v3.2.0
            $goods_query_builder = $goods_criteria->createBuilder();

            $paginator = new PaginatorQueryBuilder(array(
                'builder' => $goods_query_builder,
                'limit' => 48,
                'page' => $current_page_number,
            ));

            $this->view->page = $paginator->paginate();

            $merchant_tags = MerchantTag::find('merchant_id = ' . (int) $merchant->id);

            if ($merchant_tags && count($merchant_tags)) {
                $tags_id = [];

                foreach ($merchant_tags as $merchantTag) {
                    $tags_id[] = (int) $merchantTag->tag_id;
                }
            }

            $this->view->tags = [];
            if (isset($tags_id) && count($tags_id)) {
                $tags = Tag::find("id IN (" . implode(',', $tags_id) . ")");

                if ($tags && count($tags)) {
                    $this->view->tags = $tags;
                }
            }

            $city = $this->current_city;

            $city_title = ($city->name_case1 ? $city->name_case1 : ' города ' . $city->name);

            $this->crumbs->add('city', '/city/' . $city->name_translit, 'Товары ' . $city_title);

            if ($city->name_case1) {
                $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды ' . $city->name_case1);
            } else {
                $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды города ' . $city->name);
            }

            if ($current_category && !$current_sub_category) {
                $this->crumbs->add('merchant', '/merchant/' . $merchant->id, $merchant->name);
                $this->crumbs->add('category-name', '', $current_category->name, false);
            } elseif ($current_category && $current_sub_category) {
                $this->crumbs->add('merchant', '/merchant/' . $merchant->id, $merchant->name);
                $this->crumbs->add('category-name', '/merchant/' . $merchant->id . '/' . $current_category->name_translit, $current_category->name);
                $this->crumbs->add('subcategory-name', '', $current_sub_category->name, false);
            }else {
                $this->crumbs->add('merchant', '', $merchant->name, false);
            }

            $category_name_ru = (empty($current_category) ? "Все" : $current_category->name);

            if ($merchant->custom == 1) {
                $this->meta->setDescription("Ломбард " . $merchant->name . ". Информация о ломбарде" . $this->getPageTextForDescription($paginator->paginate()));
                $this->meta->setKeywords("Ломбард " . $merchant->name . ", " . $city->name . ", каталог ломбардов");
            } else {
                $this->meta->setDescription("Ломбард " . $merchant->name . ", товары. Информация о ломбарде" . $this->getPageTextForDescription($paginator->paginate()));
                $this->meta->setKeywords("Ломбард " . $merchant->name . ", " . $city->name . ", купить в ломбарде, б/у, объявления из ломбарда");
            }

            $this->meta->setTitle(
                    $merchant->name . ", " .
                    $city->name . ", " .
                    $merchant->address . ", " .
                    $category_name_ru .
                    $this->getPageTextForTitle($paginator->paginate())
            );

            $open_graph = new OpenGraph();
            $open_graph->setTitle($merchant->name . ", " . $city->name . ", " . $merchant->address . ", " . $category_name_ru);
            $open_graph->setType('website');
            $open_graph->setImage($merchant->getImage());

            $this->view->custom_meta_tags = $open_graph->render();

            if ($this->cookies->has('sluser')) {
                $this->view->sluser = $this->cookies->get('sluser')->getValue();

                if ($this->cookies->has('yellow_hint_hide_1')) {
                    $this->view->yellow_hint_hide_1 = $this->cookies->get('yellow_hint_hide_1')->getValue();
                }

                if ($this->cookies->has('yellow_hint_hide_2')) {
                    $this->view->yellow_hint_hide_2 = $this->cookies->get('yellow_hint_hide_2')->getValue();
                }
            } else {
                $this->view->sluser = 0;
            }

            if ($current_sub_category) {
                $this->view->pageUrl = $this->url->get('/merchant/' . $merchant->id . '/' . $current_category->name_translit . '/' . $current_sub_category->name_translit);
            } elseif ($current_category) {
                $this->view->pageUrl = $this->url->get('/merchant/' . $merchant->id . '/' . $current_category->name_translit);
            } else {
                $this->view->pageUrl = $this->url->get('/merchant/' . $merchant->id);
            }

            $this->view->abilityRequestDebt = $merchant->getAbilityRequestDebt();

            // Подкатегории
            $this->view->subcategoriesData = $subcategories_data;
            $this->view->subcategoryName = $current_subcategory_name_param;
            $this->makeSearchForm($search_form);
        }
    }

    public function allAction() {
        $city_name_param = $this->dispatcher->getParam("cityName");
        $tag_id = $this->dispatcher->getParam("tagId");
        $current_page_number = $this->request->getQuery('page', 'int'); // GET;

        if ($city_name_param && StringsHelper::isRussian($city_name_param)) {
            $translited_city_name = StringsHelper::translitRusStringToUrl($city_name_param);
            $this->response->redirect("/city/" . $translited_city_name, true, 301);

            return $this->response;
        }

        $city = City::findFirst([
                    'name = :name: OR name_translit = :name:',
                    'bind' => ['name' => $city_name_param,],
        ]);

        if ($city) {
            $this->setCurrentCity($city);
        }

        $city_title = ($city->name_case1 ? $city->name_case1 : ' города ' . $city->name);
        $this->view->cityTitle = $city_title;

        $this->crumbs->add('city', '/city/' . $city->name_translit, 'Товары ' . $city_title);

        if ($city && !empty($city->name_case1)) {
            $this->view->title = 'Ломбарды ' . $city->name_case1;
            $this->crumbs->add('city-merchant-list', '/' . $city->name . '/spisok_lombardov', 'Ломбарды ' . $this->current_city->name_case1, false);

            $this->meta->setKeywords("Ломбарды " . $city->name_case1 . ", комиссионные магазины " . $city->name_case1);
            $description = "Список ломбардов и комиссионных магазинов города ";
            $title = "Ломбарды " . $city->name_case1;
        } else {
            $this->view->title = 'Ломбарды города ' . $city->name;
            $this->crumbs->add('city-merchant-list', '/' . $city->name . '/spisok_lombardov', 'Ломбарды города ' . $city->name, false);

            $this->meta->setKeywords("Ломбарды города " . $city->name . ", комиссионные магазины города " . $city->name);
            $description = "Список ломбардов и комиссионных магазинов города ";
            $title = "Ломбарды города " . $city->name;
        }

        if ($city && !empty($city->name_case1)) {
            $this->view->title = 'Ломбарды ' . $city->name_case1;
            $this->view->titleSub = "Список ломбардов " . $city->name_case1 . ", адреса и телефоны ломбардов " . $city->name_case1 . ", ломбарды на карте.";
        } else {
            $this->view->titleSub = "Список ломбардов в городе " . $city->name . ", адреса и телефоны ломбардов в городе " . $city->name . ", ломбарды на карте.";
            $this->view->title = 'Ломбарды города ' . $city->name;
        }

        $merchants_criteria = Merchant::query()
                ->where("deleted IS NULL")
                ->orderBy("custom ASC, logo DESC");

        if ($city_name_param !== 'Все') {
            $merchants_criteria->andWhere("city = :city_name:", ['city_name' => $city->name]);
        }

        $current_merchants_criteria = clone $merchants_criteria;

        $current_tag = ($tag_id ? Tag::findFirst((int) $tag_id) : null);

        if ($current_tag) {
            $current_merchants_criteria->andWhere("EXISTS(SELECT mt.id "
                    . "FROM " . MerchantTag::class . " mt "
                    . "WHERE mt.merchant_id = " . Merchant::class . ".id "
                    . "AND mt.tag_id = :tag_id: "
                    . "LIMIT 1)", ['tag_id' => $current_tag->id]);

            $this->view->currentTag = $current_tag;
        }

        $merchants = $current_merchants_criteria->execute();

        $merchants_array = [];
        foreach ($merchants as $merchant) {
            $merchants_array[] = [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'address' => $merchant->address,
                'phone' => $merchant->phone,
                'map' => $merchant->map,
                'custom' => $merchant->custom,
                'url' => $merchant->getUrl(),
                'image_preview' => $merchant->getImagePreview(),
            ];
        }

        $this->view->merchants_json = json_encode($merchants_array);

        $mert_alias = MerchantTag::class;

        $tags = MerchantTag::query()
                ->columns([
                    "t.id",
                    "t.name",
                    "COUNT(m.id) as count_merchants",
                ])
                ->innerJoin(Tag::class, "t.id = {$mert_alias}.tag_id", "t")
                ->leftJoin(Merchant::class, "m.id = {$mert_alias}.merchant_id", "m")
                ->where("(m.id IS NULL OR (m.city = :city_name: AND m.deleted IS NULL))", ["city_name" => $city->name])
                ->orderBy("count_merchants DESC, t.name")
                ->groupBy("t.id")
                ->execute();

        $this->view->merchantTags = $tags;
        $this->view->merchantTagsAllCount = count($merchants_criteria->execute());

        $paginator = new SimplePaginatorModel([
            "data" => $merchants,
            "limit" => 48,
            "page" => $current_page_number,
        ]);

        $this->meta->setDescription($description . $city->name . $this->getPageTextForDescription($paginator->paginate()));
        $this->meta->setTitle($title . ", каталог ломбардов г. " . $city->name . $this->getPageTextForTitle($paginator->paginate()));

        $this->view->page = $paginator->paginate();
        $this->view->pageUrl = $this->url->get('/' . $city->name_translit . '/spisok_lombardov');
        $this->view->contentBlockCustomClass = "vse_lombardy_goroda";

        $this->makeSearchForm();
    }

    public function addAction() {
        $city_name_param = $this->dispatcher->getParam("cityName", 'string');

        $city = City::findFirst([
                    'name = :name: OR name_translit = :name:',
                    'bind' => ['name' => $city_name_param,],
        ]);

        if ($city->name_case1) {
            $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды ' . $city->name_case1);
        } else {
            $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды города ' . $city->name);
        }
        $this->crumbs->add('this', '', 'Добавление ломбарда', false);

        $cities = City::find(['order' => 'name']);

        $this->view->cities = $cities;
        $this->view->cityName = $city_name_param;
    }

    public function add2Action() {
        $city_name_param = $this->dispatcher->getParam("cityName", 'string');

        $city = City::findFirst([
                    'name = :name: OR name_translit = :name:',
                    'bind' => ['name' => $city_name_param,],
        ]);

        if ($city) {
            $this->setCurrentCity($city);
        }

        if ($city->name_case1) {
            $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды ' . $city->name_case1);
        } else {
            $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды города ' . $city->name);
        }

        $allowed_tags = Tag::find();

        $this->view->allowedTags = $allowed_tags;

        $this->crumbs->add('this', '', 'Добавить ломбард <!--(Шаг 2)-->', false);
    }

    public function editAction() {
        $city_name_param = $this->dispatcher->getParam("cityName", 'string');
        $merchant_id_param = (int) $this->dispatcher->getParam("id", 'int');

        $city = City::findFirst([
                    'name = :name: OR name_translit = :name:',
                    'bind' => ['name' => $city_name_param,],
        ]);

        if ($city->name_case1) {
            $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды ' . $city->name_case1);
        } else {
            $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды города ' . $city->name);
        }

        $this->crumbs->add('this', '', 'Обновить информацию о ломбарде', false);
        $this->view->id = $merchant_id_param;
        $this->view->cityName = $city_name_param;
    }

    public function edit2Action() {
        $city_name_param = $this->dispatcher->getParam("cityName", 'string');
        $merchant_id_param = (int) $this->dispatcher->getParam("id", 'int');

        $city = City::findFirst([
                    'name = :name: OR name_translit = :name:',
                    'bind' => ['name' => $city_name_param,],
        ]);

        if ($city->name_case1) {
            $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды ' . $city->name_case1);
        } else {
            $this->crumbs->add('merchants', '/' . $city->name_translit . '/spisok_lombardov', 'Ломбарды города ' . $city->name);
        }

        $exist_tags = MerchantTag::find("merchant_id = " . (int) $merchant_id_param);

        $exist_tags_ids = [];

        if ($exist_tags) {
            foreach ($exist_tags as $exist_tag) {
                $exist_tags_ids[] = $exist_tag->tag_id;
            }
        }

        $allowed_tags = Tag::find();

        if ($allowed_tags) {
            $tags = [];

            foreach ($allowed_tags as $allowed_tag) {
                $tags[] = [
                    'tag' => $allowed_tag,
                    'checked' => in_array($allowed_tag->id, $exist_tags_ids)
                ];
            }
        }

        $merchant = Merchant::findFirst((int) $merchant_id_param);

        if ($merchant) {
            $this->setCurrentMerchant($merchant);
        } else {
            $this->setCurrentCity($city);
        }

        $this->view->tags = $tags;

        $this->crumbs->add('this', '', 'Обновить информацию о ломбарде', false);
        $this->view->merchant = $merchant;
    }

}
