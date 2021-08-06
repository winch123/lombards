<?php

use Phalcon\Filter;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Paginator\RepositoryInterface;
use Polombardam\GeoIPHelper;
use PolombardamModels\City;
use PolombardamModels\Country;
use PolombardamModels\Good;
use PolombardamModels\GoodCategory;
use PolombardamModels\GoodMetal;
use PolombardamModels\GoodMetalStandart;
use PolombardamModels\GoodSubCategory;
use PolombardamModels\Merchant;
use PolombardamModels\Organization;
use PolombardamModels\Page;
use PolombardamModels\SearchForm;

class ControllerBase extends Controller {

    /**
     *
     * @var City
     */
    public $detected_city;

    /**
     *
     * @var Country
     */
    public $detected_country;

    /**
     *
     * @var City
     */
    public $remembered_city;

    /**
     *
     * @var Country
     */
    public $current_country;

    /**
     *
     * @var City
     */
    public $current_city;

    /**
     *
     * @var Merchant
     */
    public $current_merchant;

    /**
     *
     * @var Organization
     */
    public $current_merchant_network;

    /**
     *
     * @var GoodCategory
     */
    public $current_category;

    /**
     *
     * @var GoodSubCategory
     */
    public $current_subcategory;

    /**
     *
     * @var GoodMetal
     */
    public $current_metal;

    /**
     *
     * @var GoodMetalStandart
     */
    public $current_metal_standart;

    /**
     *
     * @var GoodMetalStandart
     */
    public $current_min_size;

    /**
     *
     * @var GoodMetalStandart
     */
    public $current_max_size;

    /**
     *
     * @var Good
     */
    public $current_good;

    /**
     *
     * @return string
     */
    protected function getCityNameParam() {
        // Если выбраны все города параметр будет all, если все города определенной страны параметр имеет форму all_country_<код страны>
        return explode('_', $this->request->get('city', 'string'))[0];
    }

    /**
     *
     * @return string
     */
    protected function getCountryCodeParam() {
        // Если выбраны все города определенной страны параметр имеет форму all_country_<код страны>
        $city_param = explode('_', $this->request->get('city', 'string'));

        return ($city_param[1] == 'country' ? $city_param[2] : null);
    }

    /**
     *
     * @param Country $country
     * @return $this
     */
    protected function setDetectedCountry(Country $country) {
        $this->detected_country = $country;
        $this->view->detectedCountry = $country;

        return $this;
    }

    /**
     *
     * @param City $city
     * @return $this
     */
    protected function setDetectedCity(City $city) {
        $this->detected_city = $city;
        $this->view->detectedCity = $city;

        return $this;
    }

    /**
     *
     * @param City $city
     * @return $this
     */
    protected function setRememberedCity(City $city) {
        $this->remembered_city = $city;
        $this->view->currentRememberedCity = $city;

        return $this;
    }

    /**
     *
     * @param Country $country
     * @return $this
     */
    protected function setCurrentCountry(Country $country) {
        $this->current_country = $country;
        $this->view->currentCountry = $country;

        return $this;
    }

    /**
     *
     * @param City $city
     * @return $this
     */
    protected function setCurrentCity(City $city) {
        $this->current_city = $city;
        $this->view->currentCity = $city;

        $country = Country::findFirst((int) $city->country_id);

        if ($country) {
            $this->setCurrentCountry($country);
        }

        return $this;
    }

    /**
     *
     * @param Merchant $merchant
     * @return $this
     */
    protected function setCurrentMerchant(Merchant $merchant) {
        $this->current_merchant = $merchant;
        $this->view->currentMerchant = $merchant;

        if (!$this->current_city) {
            $this->setCurrentCity($merchant->getCity());
        }

        return $this;
    }

    /**
     *
     * @param Organization $merchant_network
     * @return $this
     */
    protected function setCurrentMerchantNetwork(Organization $merchant_network) {
        $this->current_merchant_network = $merchant_network;
        $this->view->currentMerchantNetwork = $merchant_network;

        return $this;
    }

    /**
     *
     * @param GoodCategory $category
     * @return $this
     */
    protected function setCurrentCategory(GoodCategory $category) {
        $this->current_category = $category;
        $this->view->currentCategory = $category;

        return $this;
    }

    /**
     *
     * @param GoodSubCategory $subcategory
     * @return $this
     */
    protected function setCurrentSubCategory(GoodSubCategory $subcategory) {
        $this->current_subcategory = $subcategory;
        $this->view->currentSubCategory = $subcategory;

        if (!$this->current_category) {
            $this->setCurrentCategory($subcategory->getCategory());
        }

        return $this;
    }

    /**
     *
     * @param GoodMetal $metal
     * @return $this
     */
    protected function setCurrentMetal(GoodMetal $metal) {
        $this->current_metal = $metal;
        $this->view->currentMetal = $metal;

        return $this;
    }

    /**
     *
     * @param GoodMetalStandart $metal_standart
     * @return $this
     */
    protected function setCurrentMetalStandart(GoodMetalStandart $metal_standart) {
        $this->current_metal_standart = $metal_standart;
        $this->view->currentMetalStandart = $metal_standart;

        if (!$this->current_metal) {
            $this->setCurrentMetal($metal_standart->getMetal());
        }

        return $this;
    }

    /**
     *
     * @param Good $good
     * @return $this
     */
    protected function setCurrentGood(Good $good) {
        $this->current_good = $good;
        $this->view->currentGood = $good;

        if (!$this->current_merchant) {
            $this->setCurrentMerchant($good->getMerchant());
        }

        $subcategory = $good->getSubCategory();
        if ($subcategory && !$this->current_subcategory) {
            $this->setCurrentSubCategory($subcategory);
        } elseif (!$this->current_category) {
            $this->setCurrentCategory($good->getCategory());
        }

        if ($this->current_category && $this->current_category->name === 'Ювелирные изделия' &&
                !$this->current_metal_standart && $good->metal_id && $good->metal_standart_id) {
            // fix for some goods have metal_id and metal_standart_id when not a jewelry
            $metal_standart = $good->getMetalStandart();

            if ($metal_standart && !$metal_standart->system) {
                // хак из-за того что не системные пробы и металлы не имеют связей в таблице `good_metal_standarts_relations`
                $this->setCurrentMetal($good->getMetal());
            }

            $this->setCurrentMetalStandart($metal_standart);
        }

        return $this;
    }

    /**
     *
     * @param Dispatcher $dispatcher
     * @return array
     */
    protected function makeMenuItems($dispatcher) {
        $pages = Page::find([
                    "parent = 'index' AND disabled IS NULL",
                    'order' => 'sort'
        ]);

        $menu = [];
        foreach ($pages as $page) {
            $menu[] = [
                'title' => $page->title,
                'url' => "/" . ($page->controller == 'static' ? 'article' : $page->controller) . ($page->action == 'index' ? '/' : '/' . $page->action),
                'active' => $page->controller == $dispatcher->getControllerName() && $page->action == $dispatcher->getParam('page'),
            ];
        }

        return $menu;
    }

    /**
     *
     * @param SearchForm $search_form
     * @return SearchForm
     */
    protected function makeSearchForm($search_form = null) {
        if (!isset($search_form)) {
            $search_form = new SearchForm($this);
        }

        $this->view->searchForm = $search_form->generate();

        return $search_form;
    }

    /**
     *
     * @param GeoIPHelper $geo_ip_object
     * @return City|null
     */
    protected static function detectCityByGeoIpObj($geo_ip_object) {
        return City::findFirst([
                    'name = :name: OR name_translit = :name_translit:',
                    'bind' => [
                        'name' => $geo_ip_object->city_name_ru,
                        'name_translit' => $geo_ip_object->city_name_en,
                    ]
        ]);
    }

    /**
     *
     * @param GeoIPHelper $geo_ip_object
     * @return Country|null
     */
    protected static function detectCountryByGeoIpObj($geo_ip_object) {
        return Country::findFirst([
                    'code = :code:',
                    'bind' => ['code' => $geo_ip_object->country_code]
        ]);
    }

    /**
     *
     * @param Dispatcher $dispatcher
     */
    public function beforeExecuteRoute($dispatcher) {
        if ($_GET['sl_org']) {
            $this->setCookieSameSite('sluser', $_GET['sl_org'], time() + 9999 * 86400);
        }

        $cookie_remembered_city = $this->cookies->get('remember_city')->getValue();

        if ($cookie_remembered_city) {
            $remembered_city = City::findFirst((int) $cookie_remembered_city);

            $this->setRememberedCity($remembered_city);
        } else {
            $geo_ip_object = GeoIPHelper::fromIpAddress($_SERVER['REMOTE_ADDR']);
            $detected_city = $this->detectCityByGeoIpObj($geo_ip_object);
            $detected_country = $this->detectCountryByGeoIpObj($geo_ip_object);

            if ($detected_city) {
                $this->setDetectedCity($detected_city);
            }

            if ($detected_country) {
                $this->setDetectedCountry($detected_country);
            }
        }

        /*
         * Shared Meta Data
         */
        $this->meta->setData(
                $dispatcher->getControllerName(), $dispatcher->getActionName()
        );

        /*
         * Navigation Menu block
         */
        $this->view->menu = $this->makeMenuItems($dispatcher);

        /*
         * Footer block
         */
        $this->view->countGoods = number_format(Good::getTotalVisibleCount(), 0, ',', ' ');
        $this->view->countMerchants = number_format(Merchant::getTotalVisibleCount(), 0, ',', ' ');
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $samesite
     * @param string $domain
     * @param boolean $secure
     * @param boolean $httponly
     */
    protected function setCookieSameSite($name, $value, $expire = 0, $path = '/', $samesite = "Lax", $domain = null, $secure = false, $httponly = false)
    {
        if (PHP_VERSION_ID < 70300) {
            $this->cookies->set($name, $value, $expire, $path . "; samesite=" . $samesite);
        } else {
            setcookie($name, $value, [
                'expires' => $expire,
                'path' => $path,
                'samesite' => $samesite,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => $httponly
            ]);
        }
    }

    /**
     *
     * @param RepositoryInterface $paginator
     * @return string
     */
    function getPageTextForTitle(RepositoryInterface $paginator): string
    {
        return ($paginator->getCurrent() > 1 ? ', страница ' . $paginator->getCurrent() : '');
    }

    /**
     *
     * @param RepositoryInterface $paginator
     * @return string
     */
    function getPageTextForDescription(RepositoryInterface $paginator): string
    {
        return ($paginator->getCurrent() > 1 ? ', страница ' . $paginator->getCurrent() . ' из ' . $paginator->getLast() : '');
    }

}
