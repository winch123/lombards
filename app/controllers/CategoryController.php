<?php

use Phalib\Paginator\Adapter\SimplePaginatorModel;
use PolombardamModels\Good;
use PolombardamModels\GoodCategory;
use PolombardamModels\GoodSubCategory;
use PolombardamModels\SearchForm;

class CategoryController extends ControllerBase {

    public function initialize() {
        $this->crumbs->add('home', '/', 'Главная');
    }

    public function indexAction() {
        $category_name_param = $this->dispatcher->getParam("category", 'string');
        $current_page_number = $this->request->getQuery('page', 'int');
        $current_sub_category_name_param = $this->dispatcher->getParam("subcategory");

        $gc_alias = GoodCategory::class;

        $categories = GoodCategory::query()
                ->columns([
                    "{$gc_alias}.id",
                    "{$gc_alias}.name",
                    "{$gc_alias}.system",
                    "COUNT(g.id) as rowcount"
                ])
                ->innerJoin(Good::class, "g.category_id = {$gc_alias}.id", "g")
                ->where("g.deleted IS NULL")
                ->andWhere("g.hidden IS NULL")
                ->andWhere("g.sold IS NULL")
                ->andWhere("g.withdrawn IS NULL")
                // for sure
                ->andWhere("{$gc_alias}.parent_id = 0")
                ->groupBy("g.category_id")
                ->orderBy("{$gc_alias}.sort DESC, {$gc_alias}.name")
                ->execute();

        $category_other_count = 0;
        $categories_filter = [];

        foreach ($categories as $category) {
            if ($category->system) {
                $categories_filter[] = $category;
            } else {
                $category_other_count += (int) $category->rowcount;
            }
        }

        if ($category_other_count) {
            $other_category = new stdClass();
            $other_category->name = "Прочее";
            $other_category->rowcount = $category_other_count;

            $categories_filter[] = $other_category;
        }

        $current_category = GoodCategory::findFirst([
                    'name = :category_name: AND parent_id = 0 AND system = 1',
                    'bind' => [
                        'category_name' => $category_name_param,
                    ],
        ]);

        $subcategories_filter = [];

        if ($current_category) {
            $this->setCurrentCategory($current_category);

            $gsc_alias = GoodSubCategory::class;

            $subcategories = GoodSubCategory::query()
                    ->columns([
                        "{$gsc_alias}.id",
                        "{$gsc_alias}.name",
                        "{$gsc_alias}.system",
                        "COUNT(g.id) as rowcount"
                    ])
                    ->innerJoin(Good::class, "g.subcategory_id = {$gsc_alias}.id", "g")
                    ->where("g.deleted IS NULL")
                    ->andWhere("g.hidden IS NULL")
                    ->andWhere("g.sold IS NULL")
                    ->andWhere("g.withdrawn IS NULL")
                    ->andWhere("{$gsc_alias}.parent_id = :parent_id:", ["parent_id" => $current_category->id])
                    ->groupBy("{$gsc_alias}.id")
                    ->orderBy("{$gsc_alias}.sort DESC, {$gsc_alias}.name")
                    ->execute();

            $count_goods_from_subcateory = 0;
            $subcategory_other_count = 0;

            foreach ($subcategories as $subcategory) {
                $count_goods_from_subcateory += $subcategory->rowcount;

                if ($subcategory->system) {
                    $subcategories_filter[] = $subcategory;
                } else {
                    $subcategory_other_count += (int) $subcategory->rowcount;
                }
            }

            foreach ($categories as $category) {
                if ($category->id == $current_category->id) {
                    $total_without_subcat = $category->rowcount - $count_goods_from_subcateory;
                }
            }

            if ($this->dispatcher->getParam("subcategory")) {
                /*
                 * В теории подкатегория может встречаться в разных категориях.
                 * Если нет то имеет смысл сначала искать подкатегорию и у неё просто брать parent_id
                 */
                $current_sub_category = GoodSubCategory::findFirst([
                            'name = :subcategory_name: AND parent_id = :parent_cat_id: AND system = 1',
                            'bind' => [
                                'subcategory_name' => $this->dispatcher->getParam("subcategory", 'string'),
                                'parent_cat_id' => $current_category->id,
                            ],
                ]);

                if ($current_sub_category) {
                    $this->setCurrentSubCategory($current_sub_category);
                }
            }
        }

        $search_form = new SearchForm($this);

        $g_alias = Good::class;

        $goods_criteria = Good::query()
                ->innerJoin(GoodCategory::class, "{$g_alias}.category_id = cat.id", "cat")
                ->where("deleted IS NULL")
                ->andWhere("hidden IS NULL")
                ->andWhere("sold IS NULL")
                ->andWhere("withdrawn IS NULL")
                ->orderBy("bonus DESC, date DESC");

        if ($current_category) {
            $goods_criteria->andWhere("category_id = :category_id:", ["category_id" => $current_category->id]);

            if ($current_sub_category) {
                $goods_criteria->innerJoin(GoodSubCategory::class, "{$g_alias}.subcategory_id = subcat.id", "subcat")
                        ->andWhere("subcategory_id = :subcategory_id:", ["subcategory_id" => $current_sub_category->id]);
            } elseif (trim($this->dispatcher->getParam("subcategory")) === 'Прочие подкатегории') {
                $goods_criteria->innerJoin(GoodSubCategory::class, "{$g_alias}.subcategory_id = subcat.id", "subcat")
                        ->andWhere("subcat.system = 0");

                $search_form->showCustomSubCategories();
            } else {
                // Виртуальная категория "Без подкатегории"
                $goods_criteria->andWhere("{$g_alias}.category_id IS NOT NULL");
                $goods_criteria->andWhere("{$g_alias}.subcategory_id IS NULL");

                $search_form->showWithoutSubCategories();
            }
        } else {
            // Виртуальная категория "Прочие подкатегории"
            $goods_criteria->andWhere("cat.system = 0");

            $search_form->showCustomCategories();
        }

        $goods = $goods_criteria->execute();

        $paginator = new SimplePaginatorModel([
            "data" => $goods,
            "limit" => 48,
            "page" => $current_page_number
        ]);

        $this->meta->setTitle($category_name_param . $this->getPageTextForTitle($paginator->paginate()));
        $this->meta->setDescription($category_name_param . $this->getPageTextForDescription($paginator->paginate()));
        $this->meta->setKeywords($category_name_param);

        $this->view->currentCategory = $current_category;
        $this->view->categories = $categories_filter;
        $this->view->categoryName = $category_name_param;
        // Подкатегории
        $this->view->currentSubCategory = $current_sub_category;
        $this->view->subcategories = $subcategories_filter;

        $this->view->subcategory_other_count = $subcategory_other_count;
        $this->view->total_without_subcat = $total_without_subcat;
        $this->view->currentSubCategoryName = $current_sub_category_name_param;

        $this->view->page = $paginator->paginate();
        $this->view->pageUrl = $this->url->get('/category/' . $category_name_param);
        $this->crumbs->add('categoryName', '/', $category_name_param, false);

        $this->makeSearchForm($search_form);
    }

}
