<?php

namespace PolombardamModels;

class Country extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $code;

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
    public $sort;

    /**
     *
     * Страны отображаемые в закладках
     *
     * @var array
     */
    private static $countries_tabs = ['RU', 'KZ', 'UA'];

    public function initialize() {
        $this->setSource('country');
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
     * @return self[]
     */
    public static function getAllSorted() {
        return self::query()
                        ->orderBy('IF(sort IS NULL, 1, 0), sort, name')
                        ->execute();
    }

    /**
     *
     * @param array $countries
     * @return array;
     */
    public static function getForTabs($countries) {
        $return = [];

        foreach ($countries as $country) {
            if (in_array($country['code'], self::$countries_tabs)) {
                $return[] = $country;
            }
        }

        return $return;
    }

    /**
     *
     * @param array $countries
     * @return array;
     */
    public static function getForLinks($countries) {
        $return = [];

        foreach ($countries as $country) {
            $key = (in_array($country['code'], self::$countries_tabs) ? $country['code'] : 'other');
            $return[$key][] = $country;
        }

        return $return;
    }

    /**
     *
     * @param string $country_code
     * @return string
     */
    public static function getCodeForTabs($country_code) {
        return (in_array($country_code, self::$countries_tabs) ? $country_code : 'other');
    }

}
