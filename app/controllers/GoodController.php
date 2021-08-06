<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\View;
use Phalib\Helpers\OpenGraph;
use Polombardam\StringsHelper;
use PolombardamModels\Good;
use PolombardamModels\GoodSpecification;
use PolombardamModels\Merchant;
use PolombardamModels\Organization;
use Polombardam\CacheWrapper;

class GoodController extends ControllerBase {

    public function initialize() {
        $this->crumbs->add('home', '/', 'Главная');
    }

    public function indexAction() {

    }

    public function showGoodAction() {
        $good_id_param = (int) $this->dispatcher->getParam("goodId", 'int');
        $city_name_param = $this->dispatcher->getParam("cityName", 'string');

        if ($city_name_param && StringsHelper::isRussian($city_name_param)) {
            $translited_city_name = StringsHelper::translitRusStringToUrl($city_name_param);
            $this->response->redirect("/" . $translited_city_name . "/good/" . $good_id_param, true, 301);

            return $this->response;
        }

        $good = Good::findFirst((int) $good_id_param);

        if (!$good || $good->deleted == 1) {
            $response = new Response();
            $response->redirect("index/route404");
            return $response;
        }

        $this->setCurrentGood($good);
        $merchant = $good->getMerchant();

        if ($merchant) {
            $organization = Organization::findFirst(intval($merchant->organization));

            if ($organization) {
                $this->view->settings = $organization->getSettings();
            }
        }

        $good->counter_all++;
        $good->save();

        if (!$this->current_merchant || $this->current_merchant->deleted == 1) {
            $response = new Response();
            $response->redirect("index/route404");
            return $response;
        }

        $city_title = ($this->current_city->name_case1 ? $this->current_city->name_case1 : ' города ' . $good->city);

        $this->crumbs->add('city-merchant', '/city/' . $good->city, 'Товары ' . $city_title);

        if ($this->current_city && $this->current_city->name_case1) {
            $this->crumbs->add('city-merchant-list', '/' . $this->current_city->name_translit . '/spisok_lombardov', 'Ломбарды ' . $this->current_city->name_case1);
        } else {
            $this->crumbs->add('city-merchant-list', '/' . $good->city . '/spisok_lombardov', 'Ломбарды города ' . $good->city);
        }

        $this->crumbs->add('merchant', $this->current_merchant->getUrl(), $this->current_merchant->name);

        $this->view->pageUrlClear = $this->url->get('/good/');

        if ($this->cookies->has('sluser')) {
            $this->view->sluser = $this->cookies->get('sluser')->getValue();
        } else {
            $this->view->sluser = 0;
        }

        $current_category = $this->current_category;
        $current_subcategory = $this->current_subcategory;

        if ($current_category) {
            $this->crumbs->add('category-link', '/merchant/' . $this->current_merchant->getId() . '/' . $current_category->name_translit, $current_category->name);
        }

        if ($current_subcategory) {
            $this->crumbs->add('subcategory-link', '/merchant/' . $this->current_merchant->getId() . '/' . $current_category->name_translit . '/' . $current_subcategory->name_translit, $current_subcategory->name);
        }

        $open_graph = new OpenGraph();
        $open_graph->setTitle($good->name . ", " . $current_category->name . ", " . $good->city);
        $open_graph->setType('website');
        $open_graph->setImage($good->getImage());

        $this->view->custom_meta_tags = $open_graph->render();

        $this->crumbs->add('good-show', '/', $good->name, false);
        $this->meta->setTitle($good->name . ", " . $current_category->name . ", " . $good->city);

        $this->meta->setKeywords("купить, покупка, " . $good->name . ", " . $current_category->name);
        $this->meta->setDescription("Объявление о продаже товара " . $good->name);

        $this->view->good_specs = $good->getRelated(GoodSpecification::class);

        $this->view->contentBlockCustomClass = "kartochka_tovara";

        $this->makeSearchForm();

        $cache_lifetime = $this->getDI()->get('config')['application']['cacheRelatedGoodsInSeconds'];
        $cache = new CacheWrapper($cache_lifetime);

        $related_goods_ids = $cache->get('related_goods_for_' . $good->getId(), function () use ($good) {
            $result = [];

            foreach ($this->getRelatedGoods($good) as $related_good) {
                $result[] = $related_good->getId();
            }

            return $result;
        });

        $this->view->related_goods = Good::query()
            ->inWhere('id', $related_goods_ids)
            ->andWhere("deleted IS NULL")
            ->andWhere("hidden IS NULL")
            ->andWhere("sold IS NULL")
            ->andWhere("withdrawn IS NULL")
            ->execute();

        $this->view->related_goods_count = count($this->view->related_goods);
    }

    /**
     * Возвращает готовый набор "Похожих товаров"
     *
     * @param Good $good
     * @return Good[]
     */
    private function getRelatedGoods(Good $good) {
        $g_alias = Good::class;

        $related_goods_base_criteria = Good::query()
                ->innerJoin(Merchant::class, "mer.id = {$g_alias}.merchant", "mer")
                ->where("{$g_alias}.deleted IS NULL")
                ->andWhere("{$g_alias}.hidden IS NULL")
                ->andWhere("{$g_alias}.sold IS NULL")
                ->andWhere("{$g_alias}.withdrawn IS NULL")
                ->andWhere("{$g_alias}.city = :city_name:", ["city_name" => $good->city])
                ->andWhere("{$g_alias}.id != :good_id:", ["good_id" => $good->id])
                ->andWhere("mer.deleted IS NULL")
                ->orderBy("{$g_alias}.bonus DESC, {$g_alias}.date DESC");

        // Похожие товары по наименованию
        $goods_name_related_criteria = clone $related_goods_base_criteria;
        $goods_name_related_criteria->andWhere("CONCAT(' ', {$g_alias}.name, ' ') LIKE :good_name:", ['good_name' => '% ' . $good->name . ' %']);
        // лимит 7 чтобы узнать есть ли там дальше товары, но сам 7ой не должен выводиться
        $name_related_goods = $goods_name_related_criteria->limit(7)->execute();

        // Похожие товары по (под)категории
        $goods_category_related_criteria = clone $related_goods_base_criteria;

        if ($good->subcategory_id) {
            // если есть подкатегория то ищем только по ней
            $goods_category_related_criteria->andWhere("{$g_alias}.subcategory_id = '" . $good->subcategory_id . "'");
        } else {
            $goods_category_related_criteria->andWhere("{$g_alias}.category_id = '" . $good->category_id . "'");
        }

        if ($good->price) {
            // Отдельная критерия с похожими товарами по (под)категории с привязкой по цене
            $goods_category_related_criteria_with_price = clone $goods_category_related_criteria;
            $goods_category_related_criteria_with_price
                    // +/- 20%
                    ->andWhere("{$g_alias}.price >= '" . ($good->price - $good->price * 0.2) . "'")
                    ->andWhere("{$g_alias}.price <= '" . ($good->price + $good->price * 0.2) . "'");

            // лимит 7 чтобы узнать есть ли там дальше товары, но сам 7ой не должен выводиться
            $category_related_goods = $goods_category_related_criteria_with_price->limit(7)->execute();
        } else {
            // если цена у товара не указана то отображаем похожие по (под)категории без привязки по цене
            $category_related_goods = $goods_category_related_criteria->limit(7)->execute();
        }

        if ((count($name_related_goods) + count($category_related_goods)) < 6) {
            /*
             * Если в похожих товарах по (под)категории с привязкой по цене
             * плюс в похожих по названию менее 6 наименований то исключаем условие
             * "по цене" в похожих (под)категориях
             */
            $category_related_goods = $goods_category_related_criteria->limit(7)->execute();
        }

        // максимальное кол-во похожих товаров
        $max_related_goods_count = 6;
        $name_related_goods_count = count($name_related_goods);
        $category_related_goods_count = count($category_related_goods);

        if ($name_related_goods_count >= 3 && $category_related_goods_count >= 3) {
            // если в каждом по 3 и более - отображаем пополам
            $name_related_goods_limit = 3;
            $category_related_goods_limit = 3;
        } elseif ($category_related_goods_count < 3) {
            // если в категориях меньше 3х то отображаем их, а на остаток отображаем по названиям(может быть меньше, но не больше)
            $name_related_goods_limit = $max_related_goods_count - $category_related_goods_count;
            $category_related_goods_limit = $category_related_goods_count;
        } elseif ($name_related_goods_count < 3) {
            // если в названиях меньше 3х то отображаем их, а на остаток отображаем по категориям(может быть меньше, но не больше)
            $name_related_goods_limit = $name_related_goods_count;
            $category_related_goods_limit = $max_related_goods_count - $name_related_goods_count;
        }

        $related_goods = [];
        $index = 0;
        foreach ($name_related_goods as $name_related_good) {
            if ($index >= $name_related_goods_limit || $index >= $max_related_goods_count) {
                // не превышаем лимит товаров по названию и общий лимит по похожим товарам
                break;
            }

            $related_goods[] = $name_related_good;
            $index++;
        }

        unset($name_related_goods);

        $index2 = 0;
        foreach ($category_related_goods as $category_related_good) {
            if ($index2 >= $category_related_goods_limit || $index2 >= $max_related_goods_count) {
                // не превышаем лимит товаров по категории и общий лимит по похожим товарам
                break;
            }

            $related_goods[] = $category_related_good;
            $index2++;
        }

        unset($category_related_goods);

        // Если в итоге недобор - добираем из категории товара без привязки к цене
        if (count($related_goods) < $max_related_goods_count) {
            $remains_goods_count = $max_related_goods_count - count($related_goods);

            // Похожие товары только по категории и без привязки к цене
            $goods_remain_related_criteria = clone $related_goods_base_criteria;
            $goods_remain_related_criteria->andWhere("{$g_alias}.category_id = '" . $good->category_id . "'");

            // Исключаем из поиска товары, которые уже есть в выдаче
            $exclude_goods_id = [];
            foreach ($related_goods as $related_good) {
                $exclude_goods_id[] = $related_good->id;
            }

            if ($exclude_goods_id) {
                $goods_remain_related_criteria->notInWhere("{$g_alias}.id", $exclude_goods_id);
            }

            $remain_related_goods = $goods_remain_related_criteria->limit($remains_goods_count)->execute();

            foreach ($remain_related_goods as $remain_related_good) {
                if (count($related_goods) >= $max_related_goods_count) {
                    // не превышаем общий лимит похожих товаров
                    break;
                }

                $related_goods[] = $remain_related_good;
            }
        }

        return $related_goods;
    }

}
