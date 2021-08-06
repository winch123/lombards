<?php

namespace PolombardamModels;

use Exception;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Di;
use Phalcon\Filter;
use Phalcon\Http\Client\Provider\Curl;
use Phalcon\Http\Client\Provider\Stream;
use Phalcon\Http\Client\Request;
use Polombardam\CacheWrapper;

class Merchant extends ModelBase {

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var int
     */
    public $organization;

    /**
     *
     * @var int
     */
    public $workplace;

    /**
     *
     * @var int
     */
    public $custom;

    /**
     *
     * @var int
     */
    public $deleted;

    /**
     *
     * @var int
     */
    public $new;

    /**
     *
     * @var string
     */
    public $shortlink;

    /**
     *
     * @var string
     */
    public $city;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $address;

    /**
     *
     * @var string
     */
    public $phone;

    /**
     *
     * @var string
     */
    public $site;

    /**
     *
     * @var string
     */
    public $logo;

    /**
     *
     * @var string
     */
    public $map;

    /**
     *
     * @var string
     */
    public $description;

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
    public $parent;

    /**
     *
     * @var string
     */
    public $added;

    /**
     *
     * @var string
     */
    public $working_hours;

    /**
     *
     * @var string
     */
    public $modified;

    /**
     *
     * @var int
     */
    public $ability_request_debt;

    /**
     *
     * @param array $data_array
     * @return array
     */
    public static function addFromApi($data_array) {
        $messages = [];

        $exist_merchant = Merchant::findFirst(
                        "organization = " . (int) $data_array['data']['organization'] . " "
                        . "AND workplace = " . (int) $data_array['data']['workplace']
        );

        if ($exist_merchant) {
            $messages[] = [false, 'merchant-add', $data_array['data']['organization'] . "-" . $data_array['data']['workplace'], 'Merchant already exist'];

            $is_new_merchant = false;
            $new_merchant = $exist_merchant;
        } else {
            $is_new_merchant = true;
            $new_merchant = new Merchant();

            $new_merchant->organization = (int) $data_array['data']['organization'];
            $new_merchant->workplace = (int) $data_array['data']['workplace'];
        }

        $new_merchant->deleted = null;
        $new_merchant->city = trim($data_array['data']['city']);
        $new_merchant->name = (string) $data_array['data']['name'];
        $new_merchant->address = (string) $data_array['data']['address'];
        $new_merchant->phone = (string) $data_array['data']['phone'];
        $new_merchant->description = (string) $data_array['data']['description'];
        // необходимо для сортировки в админке
        $new_merchant->modified = date("Y-m-d H:i:s");

        if (!empty($data_array['data']['shortlink'])) {
            $new_merchant->updateShortlink($data_array['data']['shortlink']);
        }

        $new_merchant->ability_request_debt = (int) $data_array['data']['ability_request_debt'];

        $new_merchant->checkCityExist();

        $new_merchant->requestYandexCoordinates();

        if ($is_new_merchant) {
            if ($new_merchant->create()) {
                $messages[] = [true, 'merchant-add', $data_array['data']['organization'] . "-" . $data_array['data']['workplace']];
            } else {
                $messages[] = [false, 'merchant-add', $data_array['data']['organization'] . "-" . $data_array['data']['workplace'], $new_merchant->getMessagesNormalized()];
            }
        } else {
            if ($new_merchant->save()) {
                $messages[] = [true, 'merchant-add', $data_array['data']['organization'] . "-" . $data_array['data']['workplace']];
            } else {
                $messages[] = [false, 'merchant-add', $data_array['data']['organization'] . "-" . $data_array['data']['workplace'], $new_merchant->getMessagesNormalized()];
            }
        }

        $new_merchant->fillImage($data_array['data']['image']);

        $city = City::findFirst(['name = :city_name:', 'bind' => ['city_name' => $new_merchant->city]]);
        (new CacheWrapper)->delete('generateMerchantList', ['current_city' => $city->id]);

        return $messages;
    }

    /**
     *
     * @param int $organization_id
     * @param array $data_array
     * @return array
     */
    public static function editFromApi($organization_id, $data_array) {
        $messages = [];

        $merchant_to_edit = Merchant::findFirst(
                        "organization = " . (int) $organization_id . " AND " .
                        "workplace = " . (int) $data_array['data']['workplace']
        );

        if ($merchant_to_edit) {
            $city_updated = null;
            $address_updated = null;

            if (isset($data_array['data']['city']) && $merchant_to_edit->city != $data_array['data']['city']) {
                $merchant_to_edit->checkCityExist();
                $merchant_to_edit->city = trim($data_array['data']['city']);
                $city_updated = true;
            }

            if (isset($data_array['data']['address']) && $merchant_to_edit->address != $data_array['data']['address']) {
                $merchant_to_edit->address = (string) $data_array['data']['address'];
                $address_updated = true;
            }

            if ($city_updated || $address_updated) {
                $merchant_to_edit->requestYandexCoordinates();
            }

            if (isset($data_array['data']['name'])) {
                $merchant_to_edit->name = (string) $data_array['data']['name'];
            }

            if (isset($data_array['data']['phone'])) {
                $merchant_to_edit->phone = (string) $data_array['data']['phone'];
            }

            if (isset($data_array['data']['description'])) {
                $merchant_to_edit->description = (string) $data_array['data']['description'];
            }

            if (isset($data_array['data']['shortlink'])) {
                $merchant_to_edit->updateShortlink($data_array['data']['shortlink']);
            }

            $merchant_to_edit->modified = date("Y-m-d H:i:s");

            if (isset($data_array['data']['ability_request_debt'])) {
                $merchant_to_edit->ability_request_debt = (int) $data_array['data']['ability_request_debt'];
            }

            if ($merchant_to_edit->save()) {
                $messages[] = [true, 'merchant-edit', $organization_id . "-" . $data_array['data']['workplace']];
            } else {
                $messages[] = [false, 'merchant-edit', $organization_id . "-" . $data_array['data']['workplace'], $merchant_to_edit->getMessagesNormalized()];
            }

            if (isset($data_array['data']['image'])) {
                $merchant_to_edit->fillImage($data_array['data']['image']);
            }
        } else {
            $messages[] = [false, 'merchant-edit', $organization_id . "-" . $data_array['data']['workplace'], 'Merchant not exist'];
        }

        return $messages;
    }

    /**
     *
     * @param int $organization_id
     * @param array $data_array
     * @return array
     */
    public static function removeFromApi($organization_id, $data_array) {
        $messages = [];

        $merchant_to_remove = Merchant::findFirst(
                        "organization = " . (int) $organization_id . " "
                        . "AND workplace = " . (int) $data_array['data']['workplace']
        );

        if ($merchant_to_remove) {
            $city = City::findFirst(['name = :city_name:', 'bind' => ['city_name' => $merchant_to_remove->city]]);
            (new CacheWrapper)->delete('generateMerchantList', ['current_city' => $city->id]);

            $delete_goods_result = $merchant_to_remove->markAllGoodsDeleted();

            if ($delete_goods_result) {
                $messages[] = [false, 'merchant-remove', $organization_id . "-" . $data_array['data']['workplace'], $delete_goods_result];
            }

            $merchant_to_remove->deleted = 1;
            $merchant_to_remove->city = null;
            $merchant_to_remove->name = null;
            $merchant_to_remove->address = null;
            $merchant_to_remove->phone = null;
            $merchant_to_remove->site = null;
            $merchant_to_remove->logo = null;
            $merchant_to_remove->map = null;
            $merchant_to_remove->description = null;
            $merchant_to_remove->shortlink = null;

            if ($merchant_to_remove->save()) {
                $messages[] = [true, 'merchant-remove', $organization_id . "-" . $data_array['data']['workplace']];
            } else {
                $messages[] = [false, 'merchant-remove', $organization_id . "-" . $data_array['data']['workplace']];
            }
        } else {
            $messages[] = [false, 'merchant-remove', $organization_id . "-" . $data_array['data']['workplace'], 'Merchant not exist'];
        }

        return $messages;
    }

    public function initialize() {
        $this->hasMany("id", MerchantImage::class, "merchant_id");
        $this->belongsTo("city", City::class, "name");

        $this->setSource('merchant');
    }

    /**
     *
     * @param Stream|Curl $provider
     */
    private function loadYandexGeo($provider) {
        $geocoder_api_key = $this->getDI()->get('config')['yandex_maps']['api_key'];
        $support_email = 'support@smartlombard.ru';

        if ($geocoder_api_key) {
            $response_object = $provider->get(
                    '?format=json&apikey=' . $geocoder_api_key . '&results=1&geocode=' . $this->city . ', ' .
                    $this->address);

            $response = json_decode($response_object->body, true);

            if (isset($response['response'])) {
                $point = $response['response']['GeoObjectCollection']['featureMember']
                        [0]['GeoObject']['Point']['pos'];

                $this->map = $point;
            } else {
                if (APPLICATION_ENV === ENV_PRODUCTION) {
                    mail($support_email, 'Интеграция с Яндекс.Картами', 'ВНИМАНИЕ: При запросе координат сервис вернул неожиданный ответ!');
                }

                if (APPLICATION_ENV === ENV_DEVELOPMENT) {
                    throw new Exception('Сервис Яндекс.Карт вернул неожиданный ответ');
                }
            }
        } else {
            if (APPLICATION_ENV === ENV_PRODUCTION) {
                mail($support_email, 'Интеграция с Яндекс.Картами', 'ВНИМАНИЕ: Не указан API ключ для получения координат филиалов!');
            }

            throw new Exception('Не указан API ключ для работы с Яндекс.Картами');
        }
    }

    /**
     *
     * @param Request $provider
     */
    public function requestYandexCoordinates($provider = null) {
        $city = trim($this->city);
        $address = trim($this->address);

        if ($city && $address) {
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
    public function getImagePreview() {
        $noimage = "/static/img/noimage.gif";

        if (empty($this->custom)) {
            $images = $this->getRelated(MerchantImage::class);

            if ($images && count($images) <= 0) {
                return $noimage;
            }

            if (isset($images[0])) {
                return $images[0]->preview;
            }
        } else {
            if (empty($this->logo)) {
                return $noimage;
            } else {
                if (file_exists(APP_PATH . "/public/static/img/merchant/" . $this->logo)) {
                    return "/static/img/merchant/" . $this->logo;
                } else {
                    return $noimage;
                }
            }
        }
    }

    /**
     *
     * @return string
     */
    public function getImage() {
        $noimage = "/static/img/noimage.gif";

        if (empty($this->custom)) {
            $images = $this->getRelated(MerchantImage::class);

            if ($images && count($images) <= 0) {
                return $noimage;
            }

            if (isset($images[0])) {
                return $images[0]->src;
            }
        } else {
            if (empty($this->logo)) {
                return $noimage;
            } else {
                if (file_exists(APP_PATH . "/public/static/img/merchant/" . $this->logo)) {
                    return "/static/img/merchant/" . $this->logo;
                } else {
                    return $noimage;
                }
            }
        }
    }

    /**
     *
     * @return boolean
     */
    public function isHaveLogo() {
        if ($this->custom) {
            if (empty($this->logo)) {
                return false;
            } else {
                if (file_exists(APP_PATH . "/public/static/img/merchant/" . $this->logo)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return (bool) count($this->getRelated(MerchantImage::class));
        }
    }

    /**
     * Состоит в сети ломбардов/магазинов
     *
     * @return bool
     */
    public function isBelongsToNetwork() {
        $merchants_in_network_count = Merchant::count([
                    "organization = :organization_id: AND deleted IS NULL",
                    'bind' => [
                        'organization_id' => $this->organization,
                    ]
        ]);

        return $merchants_in_network_count > 1;
    }

    /**
     *
     * @param string $new_shortlink
     * @return $this
     */
    public function updateShortlink($new_shortlink) {
        $filter = new Filter();
        $merchant_shortlink = trim($filter->sanitize($new_shortlink, "string"));

        $this->shortlink = ($merchant_shortlink ?: null);

        return $this;
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
    public function getUrl() {
        if (empty($this->shortlink)) {
            return "/merchant/" . $this->id;
        } else {
            return '/' . $this->shortlink;
        }
    }

    /**
     *
     * @return string
     */
    public function getNetworkUrl() {
        return Organization::getUrlByOrganizationId($this->organization);
    }

    /**
     *
     * @return string|null
     */
    public function getMap() {
        if ($this->map) {
            return str_replace(' ', ',', $this->map);
        } else {
            return null;
        }
    }

    public static function recount_good() {
        // Выражение на чистом SQL
        $sql = "UPDATE merchant SET "
                . "merchant.count_good = ("
                . "SELECT COUNT(good.id) "
                . "FROM good "
                . "WHERE good.merchant = merchant.id "
                . "AND merchant.custom IS NULL "
                . "AND good.deleted IS NULL "
                . "AND good.hidden IS NULL "
                . "AND good.sold IS NULL "
                . "AND good.withdrawn IS NULL)";

        // Модель
        $instance = new self();
        $instance->getWriteConnection()->execute($sql);
    }

    /**
     *
     * @param array $images
     */
    public function fillImage($images) {
        if (isset($this->id) && $this->id != 0) {
            $old_image = MerchantImage::findFirst("merchant_id = " . (int) $this->id);

            if ($old_image) {
                $old_image->delete();
            }
        }

        if (count($images)) {
            $image = $images[0];

            if (!empty($image['src'])) {
                $new_image = new MerchantImage();
                $new_image->merchant_id = $this->id;
                $new_image->src = $image['src'];
                $new_image->preview = $image['preview'];

                $new_image->save();
            }
        }
    }

    /**
     *
     * @return City
     */
    public function checkCityExist() {
        $city_name = $this->city;

        $city_exist = City::findFirst([
                    'name = :name:',
                    'bind' => [
                        'name' => $city_name,
                    ],
        ]);

        if ($city_exist) {
            return $city_exist;
        } else {
            $new_city = new City();
            $new_city->name = trim($city_name);
            $new_city->name_translit = $new_city->makeTransliteratedName();

            $new_city->requestYandexCoordinates();

            $new_city->create();

            return $new_city;
        }
    }

    /**
     *
     * @return array contain error messages
     */
    public function markAllGoodsDeleted() {
        $model_manager = $this->getModelsManager();

        $result = $model_manager->executeQuery(
                "UPDATE " . Good::class . " SET "
                . "deleted = 1, "
                . "name = NULL, "
                . "price = NULL, "
                . "size = NULL, "
                . "date = NULL, "
                . "city = NULL, "
                . "features = NULL, "
                . "category_id = NULL, "
                . "subcategory_id = NULL, "
                . "currency = NULL, "
                . "bonus = NULL, "
                . "image_studio = NULL "
                . "WHERE organization = :org_id:"
                . "AND merchant = :merchant_id: "
                . "AND deleted IS NULL",
                [
                    'org_id' => $this->organization,
                    'merchant_id' => $this->getId(),
                ]
        );

        return array_map(function ($item) {
            return (string) $item;
        }, $result->getMessages());
    }

    /**
     *
     * @return City
     */
    public function getCity() {
        return $this->getRelated(City::class);
    }

    /**
     *
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * Маскирует в телефоне после 4 первых
     *
     * @return string
     */
    public function getMaskedPhone() {
        $masked_phone = '';

        foreach (str_split(preg_replace("/[^0-9]/", '', $this->phone)) as $pos => $chr) {
            if ($pos < 4) {
                $masked_phone .= $chr;
            } else {
                $masked_phone .= 'X';
            }
        }

        return $masked_phone;
    }

    /**
     *
     * @return int
     */
    public static function getTotalVisibleCount() {
        $di = Di::getDefault();

        /** @var AdapterInterface $file_cache */
        $file_cache = $di->get('fcache');

        $merchants_count = $file_cache->get('counters.total_merchants');

        if (!isset($merchants_count)) {
            $merchants_count = Merchant::count("deleted IS NULL");

            $file_cache->set('counters.total_merchants', $merchants_count, 60 * 60);
        }

        return (int) $merchants_count;
    }

    /**
     *
     * @return int
     */
    public function getAbilityRequestDebt(): int {
        return $this->ability_request_debt;
    }

}
