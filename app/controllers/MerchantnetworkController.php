<?php

use Phalcon\Filter;
use Phalcon\Http\Response;
use Phalib\Paginator\Adapter\SimplePaginatorModel;
use Polombardam\StringsHelper;
use PolombardamModels\City;
use PolombardamModels\Merchant;
use PolombardamModels\Organization;

class MerchantnetworkController extends ControllerBase {

    public function initialize() {
        $this->crumbs->add('home', '/', 'Главная');

        $this->view->yandexMaps = true;
    }

    public function indexAction() {
        $merchant_network_id = (int) $this->dispatcher->getParam("id", 'int');
        $city_name_param = $this->dispatcher->getParam("city", 'string');
        $current_page_number = $this->request->getQuery('page', 'int');

        if ($merchant_network_id) {
            $organization = Organization::findFirst((int) $merchant_network_id);

            if (!$organization) {
                // fix network merchant not created if merchants in this network are exists
                $merchants_exist_for_network = Merchant::findFirst([
                            "organization = :network_id: AND deleted IS NULL",
                            'bind' => [
                                'network_id' => $merchant_network_id,
                            ],
                ]);

                if ($merchants_exist_for_network) {
                    $organization = new Organization();
                    $organization->id = $merchant_network_id;
                    $organization->create();
                }
            }
        } else {
            $filter = new Filter();
            $shortlink_string = $filter->sanitize(implode('/', $this->dispatcher->getParams()), 'string');

            $organization = Organization::findFirst([
                        "shortlink = :short_link:",
                        'bind' => [
                            'short_link' => $shortlink_string,
                        ],
            ]);
        }

        if (!$organization) {
            $response = new Response();
            $response->redirect("index/route404");
            return $response;
        }

        $this->setCurrentMerchantNetwork($organization);

        $city = City::findFirst([
            "name = :name: OR name_translit = :name:",
            'bind' => ['name' => $city_name_param,],
        ]);

        $network_merchants = Merchant::find([
                    'organization = :org_id: AND deleted IS NULL ',
                    'bind' => [
                        'org_id' => (int) $organization->id,
                    ],
        ]);

        if ($city) {
            $this->setCurrentCity($city);

            $filtered_network_merchants = Merchant::find([
                        'organization = :org_id: AND deleted IS NULL AND city = :city_name:',
                        'bind' => [
                            'org_id' => (int) $organization->id,
                            'city_name' => $city->name,
                        ],
            ]);
        } else {
            $filtered_network_merchants = $network_merchants;
        }

        $paginator = new SimplePaginatorModel([
            "data" => $filtered_network_merchants,
            "limit" => 48,
            "page" => $current_page_number,
        ]);

        $merchant_network_cities_name = [];
        $cities_english_name = [];
        $all_cities_count = 0;
        // по 4 на строке
        foreach ($network_merchants as $merchant) {
            $all_cities_count++;

            if (StringsHelper::isRussian($merchant->city)) {
                $translited_city_name = StringsHelper::translitRusStringToUrl($merchant->city);
            } else {
                $translited_city_name = $merchant->city;
            }

            if (!isset($merchant_network_cities_name[$merchant->city])) {
                $merchant_network_cities_name[$merchant->city] = 1;
            } else {
                $merchant_network_cities_name[$merchant->city] ++;
            }

            $cities_english_name[$merchant->city] = $translited_city_name;
        }

        $this->view->allCitysCount = $all_cities_count;
        $this->view->citys = $merchant_network_cities_name;
        $this->view->englishNames = $cities_english_name;
        $this->view->cityName = $city_name_param;
        $this->view->cityRusNameActive = array_flip($cities_english_name)[$city_name_param];

        if (count($merchant_network_cities_name)) {
            $merchant_network_first_city_name = array_keys($merchant_network_cities_name)[0];
            $this->view->city = City::findFirst([
                        'name = :city_name:',
                        'bind' => ['city_name' => $merchant_network_first_city_name[0]],
            ]);
        }

        $merchants_array = [];
        foreach ($filtered_network_merchants as $merchant) {
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

        $this->view->page = $paginator->paginate();

        $this->view->pageUrl = $this->url->get(($city ? '/' . $city->name : '') . $organization->getUrl());

        $page_title = 'Все филиалы сети' . ($organization->merchant_name ? ' ' . $organization->merchant_name : '');
        $this->view->page_title = $page_title;

        $this->meta->setTitle($page_title . $this->getPageTextForTitle($paginator->paginate()));
        $this->crumbs->add('merchantNetwork', '', 'Все филиалы сети ' . $this->current_merchant_network->merchant_name, false);
        // enable view if request was redirected from another controller(for shortlinks)
        $this->view->enable();
        $this->makeSearchForm();
        $this->view->contentBlockCustomClass = "vse_filialy_seti";
    }

}
