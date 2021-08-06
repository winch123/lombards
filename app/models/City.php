<?php

namespace PolombardamModels;

use Phalcon\Http\Client\Provider\Curl;
use Phalcon\Http\Client\Provider\Stream;
use Phalcon\Http\Client\Request;
use Polombardam\StringsHelper;
use stdClass;

class City extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $name_case1;

    /**
     *
     * @var string
     */
    public $name_case2;

    /**
     *
     * @var string
     */
    public $name_translit;

    /**
     *
     * @var string
     */
    public $old_url;

    /**
     *
     * @var int
     */
    public $count_good;

    /**
     *
     * @var int
     */
    public $count_merchant;

    /**
     *
     * @var int
     */
    public $count_merchant_custom;

    /**
     *
     * @var string
     */
    public $map;

    /**
     *
     * @var int
     */
    public $country_id;

    /**
     *
     * @param array $data_array
     * @return array
     */
    public static function addFromApi($data_array) {
        $messages = [];

        $existed_city = City::findFirst([
                    "name = :city_name:",
                    "bind" => ['city_name' => $data_array['data']['name']],
        ]);

        if (!$existed_city) {
            $new_city = new City();
            $new_city->name = trim($data_array['data']['name']);
            $new_city->name_translit = $new_city->makeTransliteratedName();

            $new_city->requestYandexCoordinates();

            if ($new_city->create()) {
                $messages[] = [true, 'city-add', $data_array['data']['name']];
            } else {
                $messages[] = [false, 'city-add', $data_array['data']['name'], $new_city->getMessagesNormalized()];
            }
        } else {
            $messages[] = [false, 'city-add', $data_array['data']['name'], 'City already exist'];
        }

        return $messages;
    }

    /**
     *
     * @param array $data_array
     * @return array
     */
    public static function editFromApi($data_array) {
        $messages = [];

        $city_to_edit = City::findFirst([
                    "name = :city_name:",
                    "bind" => ['city_name' => trim($data_array['name'])],
        ]);

        if (!$city_to_edit) {
            $messages[] = [false, 'city-edit', $data_array['name'], 'City not exist'];
        } else {
            $city_to_edit->name = trim($data_array['data']['name']);
            if ($city_to_edit->save()) {
                $messages[] = [true, 'city-edit', $data_array['name']];
            } else {
                $messages[] = [false, 'city-edit', $data_array['name'], $city_to_edit->getMessagesNormalized()];
            }
        }

        return $messages;
    }

    /**
     *
     * @param array $data_array
     * @return array
     */
    public static function removeFromApi($data_array) {
        $messages = [];

        $city_to_remove = City::findFirst([
                    "name = :city_name:",
                    "bind" => ['city_name' => trim($data_array['name'])],
        ]);

        if (!$city_to_remove) {
            $messages[] = [false, 'city-remove', $data_array['name'], 'City not exist'];
        } else {
            if ($city_to_remove->delete()) {
                $messages[] = [true, 'city-remove', $data_array['name']];
            } else {
                $messages[] = [false, 'city-remove', $data_array['name'], $city_to_remove->getMessagesNormalized()];
            }
        }

        return $messages;
    }

    public function initialize() {
        $this->setSource('city');
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
     * @return string
     */
    public function getNameCase1() {
        return $this->name_case1;
    }

    /**
     *
     * @param Stream|Curl $provider
     */
    private function loadYandexGeo($provider) {
        $response = $provider->get(
                '?format=json&results=1&kind=locality&geocode=' .
                $this->name
        );

        $response = json_decode($response->body, true);

        if (isset($response['response'])) {
            $point = $response['response']['GeoObjectCollection']['featureMember']
                    [0]['GeoObject']['Point']['pos'];

            $this->map = $point;

            $address = $response['response']['GeoObjectCollection']['featureMember']
                    [0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address'];

            $country_code = $address['country_code'];

            if ($country_code) {
                $country = Country::findFirst(["code = :code:", 'bind' => ['code' => $country_code]]);

                if (!$country) {
                    foreach ($address['Components'] as $component) {
                        if ($component['kind'] == 'country') {
                            $country = new Country([
                                'code' => $country_code,
                                'name' => $component['name'],
                            ]);

                            $country->save();
                            break;
                        }
                    }
                }

                if ($country) {
                    $this->country_id = $country->id;
                }
            }
        }
    }

    /**
     *
     * @param Request $provider
     */
    public function requestYandexCoordinates($provider = null) {
        $city_name = trim($this->name);

        if ($city_name) {
            if (!$provider) {
                $provider = Request::getProvider();
                $provider->setBaseUri('https://geocode-maps.yandex.ru/1.x/');
                $provider->header->set('Accept', '*/*');
            }

            $this->loadYandexGeo($provider);
        }
    }

    /**
     *
     * @return string
     */
    public function getFirstLetter() {
        return mb_substr($this->name, 0, 1);
    }

    /**
     *
     * @return string
     */
    public function getUrl() {
        if ($this->count_good > 0) {
            return "/city/" . $this->name_translit;
        } else {
            return "/" . $this->name_translit . "/spisok_lombardov";
        }
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
     * @return array
     */
    public static function getCitysByLetters() {
        $cities_models = City::find(['order' => 'name ASC']);

        $cities = [];

        foreach ($cities_models as $item) {
            $tmp = new stdClass();
            $tmp->id = $item->id;
            $tmp->name = $item->name;
            $tmp->name_translit = $item->name_translit;
            $tmp->count = $item->count_good;
            $tmp->country_id = $item->country_id;

            $cities[mb_strtoupper(mb_substr($item->name, 0, 1))][] = $tmp;
        }

        ksort($cities);

        $cities_count = count($cities_models);

        return [
            "citys" => $cities,
            "average" => round(
                    ((($cities_count % 2 == 0) ? $cities_count : $cities_count - 1) + (count($cities) * 2)) / 4)
        ];
    }

    /**
     *
     * @param Country[] $countries
     * @return array
     */
    public static function getChooseCitiesList($countries) {
        $cities = City::getCitysByLetters();
        $countries_arr = [];

        foreach ($countries as $country) {
            $countries_arr[$country['id']] = $country;
        }

        $letter_columns = [
            ["А", "Б", "В", "Г"],
            ["Д", "Е", "Ё", "Ж", "З", "И", "Й", "К"],
            ["Л", "М", "Н", "О", "П", "Р"],
            ["С", "Т", "У", "Ф", "Х", "Ч", "Ш", "Щ", "Э", "Ю", "Я"],
        ];

        $city_countries = [];

        foreach ($letter_columns as $index => $letters) {
            foreach ($letters as $letter) {
                if (isset($cities['citys'][$letter])) {
                    foreach ($cities['citys'][$letter] as $city) {
                        $country_code = ($countries_arr[$city->country_id]['code'] ?: 'other');
                        $city_countries[$country_code][$index][$letter][] = $city;
                    }
                }
            }
        }

        return $city_countries;
    }

    public static function recount_good() {
        $sql = "UPDATE city SET "
                . "city.count_good = ("
                . "SELECT COUNT(good.id) "
                . "FROM good "
                . "WHERE good.city = city.name "
                . "AND good.deleted IS NULL "
                . "AND good.hidden IS NULL "
                . "AND good.sold IS NULL "
                . "AND good.withdrawn IS NULL)";

        $instance = new self();
        $connection = $instance->getWriteConnection();

        $connection->query($sql);
    }

}
