<?php

namespace PolombardamModels;

class Tag extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $text;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $keywords;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $title;

    public function initialize() {
        $this->setSource('tag');
    }

    /**
     *
     * @param City $city
     * @return string
     */
    public function getName($city = null) {
        return ($city ? self::template2string($this->name, $city) : $this->name);
    }

    /**
     *
     * @param City $city
     * @return string
     */
    public function getKeywords($city = null) {
        return ($city ? self::template2string($this->keywords, $city) : $this->keywords);
    }

    /**
     *
     * @param City $city
     * @return string
     */
    public function getDescription($city = null) {
        return ($city ? self::template2string($this->description, $city) : $this->description);
    }

    /**
     *
     * @param City $city
     * @return string
     */
    public function getTitle($city = null) {
        return ($city ? self::template2string($this->title, $city) : $this->title);
    }

    /**
     *
     * @param City $city
     * @return string
     */
    public function getText($city = null) {
        return ($city ? self::template2string($this->text, $city) : $this->text);
    }

    /**
     *
     * @param string $string
     * @param City $city
     * @return string
     */
    private static function template2string($string, City $city) {
        if (!empty($string) && $city) {
            $result = str_replace(
                    ['{САМАРА}', '{САМАРЫ}', '{ВСАМАРЕ}'], [$city->name, $city->name_case1, $city->name_case2], $string
            );
        } else {
            $result = '';
        }

        return $result;
    }

}
