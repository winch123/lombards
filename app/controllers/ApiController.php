<?php

use Phalcon\Filter;
use Phalcon\Http\Client\Request;
use Phalcon\Mvc\View;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use PolombardamModels\City;
use PolombardamModels\Country;
use PolombardamModels\ErrorReport;
use PolombardamModels\Good;
use PolombardamModels\GoodCategory;
use PolombardamModels\GoodMetal;
use PolombardamModels\GoodMetalStandart;
use PolombardamModels\GoodSpecification;
use PolombardamModels\GoodStudioPhoto;
use PolombardamModels\GoodSubCategory;
use PolombardamModels\Merchant;
use PolombardamModels\MerchantTag;
use PolombardamModels\Organization;
use PolombardamModels\Partner;
use PolombardamModels\SearchForm;
use PolombardamModels\Settings;
use PolombardamModels\Tag;

class ApiController extends ControllerBase
{

    private $customResponse = [];

    public function initialize()
    {
        $this->view->disable();
        $this->response->setContentType('application/json', 'UTF-8');
    }

    private function auth()
    {
        $auth = $this->request->getHeader("AuthorizationSL");

        if (empty($auth)) {
            return false;
        } else {
            $raw_data = $this->request->getPost("data");
            $partner_id = (int) $this->request->getPost("partner");

            $partner = Partner::findFirst((int) $partner_id);

            if (!$partner) {
                return false;
            } else {
                $hash = sha1(sha1($raw_data) . $partner->hash);

                if ($auth != $hash) {
                    return false;
                }
            }
        }

        return true;
    }

    private function addToResponse($status, $type, $unique, $message = '')
    {
        $this->customResponse[] = [
            "status" => $status,
            "type" => $type,
            "unique" => $unique,
            "message" => $message
        ];
    }

    private function addToResponseArray($arr)
    {
        foreach ($arr as $a) {
            $this->customResponse[] = [
                "status" => $a[0],
                "type" => $a[1],
                "unique" => $a[2],
                "message" => isset($a[3]) ? $a[3] : ''
            ];
        }
    }

    public function indexAction()
    {
        if ($this->auth() == false) {
            return $this->response([[
                    "status" => false,
                    "type" => "auth",
                    "inique" => null,
                    "message" => "Authorization failed"
            ]]);
        }

        $raw_data = $this->request->getPost("data");
        $data_array = json_decode($raw_data, true);

        // start transaction
        $this->db->begin();

        foreach ($data_array as $lombard) {
            $organization_id = (int) $lombard['organization'];

            // First of all create network info for merchants
            $organization = Organization::findFirst((int) $organization_id);

            if (!$organization) {
                $organization = new Organization();
                $organization->id = $organization_id;
                $organization->create();
            }

            if (isset($lombard['data']['merchants'])) {
                // Только смартломбард может работать с мерчантами.
                if ($this->request->getPost("partner") == 1) {
                    foreach ($lombard['data']['merchants'] as $merchant) {
                        if ($merchant['type'] == 'add') {
                            $messages = Merchant::addFromApi($merchant);
                            $this->addToResponseArray($messages);
                        } elseif ($merchant['type'] == 'edit') {
                            $messages = Merchant::editFromApi($organization_id, $merchant);
                            $this->addToResponseArray($messages);
                        } elseif ($merchant['type'] == 'remove') {
                            $messages = Merchant::removeFromApi($organization_id, $merchant);
                            $this->addToResponseArray($messages);
                        } else {
                            $this->addToResponse(false, 'merchant-unknown', null);
                        }
                    }
                } else {
                    $this->addToResponse(false, 'auth', null, 'Access denied');
                }
            }

            if (isset($lombard['data']['organizations'])) {
                foreach ($lombard['data']['organizations'] as $organization) {
                    if ($organization['type'] == 'edit') {
                        $messages = Organization::editFromApi($organization_id, $organization);
                        $this->addToResponseArray($messages);
                    }
                }
            }

            if (isset($lombard['data']['citys'])) {
                foreach ($lombard['data']['citys'] as $city_array) {
                    if ($city_array['type'] == 'add') {
                        $messages = City::addFromApi($city_array);
                        $this->addToResponseArray($messages);
                    } elseif ($city_array['type'] == 'edit') {
                        $messages = City::editFromApi($city_array);
                        $this->addToResponseArray($messages);
                    } elseif ($city_array['type'] == 'remove') {
                        $messages = City::removeFromApi($city_array);
                        $this->addToResponseArray($messages);
                    } else {
                        $this->addToResponse(false, 'city-unknown', null);
                    }
                }
            }

            if (isset($lombard['data']['goods'])) {
                foreach ($lombard['data']['goods'] as $good) {
                    if ($good['type'] == 'add') {
                        $messages = Good::addFromApi($organization_id, $good);
                        $this->addToResponseArray($messages);
                    } elseif ($good['type'] == 'edit') {
                        $messages = Good::editFromApi($organization_id, $good);
                        $this->addToResponseArray($messages);
                    } elseif ($good['type'] == 'remove') {
                        $messages = Good::removeFromApi($organization_id, $good);
                        $this->addToResponseArray($messages);
                    } else {
                        $this->addToResponse(false, 'good-unknown', null);
                    }
                }
            }

            if (isset($lombard['data']['metals'])) {
                foreach ($lombard['data']['metals'] as $metal) {
                    if ($metal['type'] == 'edit') {
                        $messages = GoodMetal::editFromApi($organization_id, $metal);
                        $this->addToResponseArray($messages);
                    } else {
                        $this->addToResponse(false, 'metals-unknown', null);
                    }
                }
            }

            if (isset($lombard['data']['metal_standarts'])) {
                foreach ($lombard['data']['metal_standarts'] as $metal_standart) {
                    if ($metal_standart['type'] == 'edit') {
                        $messages = GoodMetalStandart::editFromApi($organization_id, $metal_standart);
                        $this->addToResponseArray($messages);
                    } else {
                        $this->addToResponse(false, 'metal-standarts-unknown', null);
                    }
                }
            }

            if (isset($lombard['data']['categories'])) {
                foreach ($lombard['data']['categories'] as $category) {
                    if ($category['type'] == 'edit') {
                        $messages = GoodCategory::editFromApi($organization_id, $category);
                        $this->addToResponseArray($messages);
                    } else {
                        $this->addToResponse(false, 'categories-unknown', null);
                    }
                }
            }

            if (isset($lombard['data']['subcategories'])) {
                foreach ($lombard['data']['subcategories'] as $category) {
                    if ($category['type'] == 'edit') {
                        $messages = GoodSubCategory::editFromApi($organization_id, $category);
                        $this->addToResponseArray($messages);
                    } else {
                        $this->addToResponse(false, 'subcategories-unknown', null);
                    }
                }
            }

            if (isset($lombard['data']['specifications'])) {
                foreach ($lombard['data']['specifications'] as $specifications) {
                    if ($specifications['type'] == 'replace_all') {
                        $partner_id = (int) $this->request->getPost("partner");

                        $messages = GoodSpecification::replaceAllFromApi($partner_id, $specifications['data']);

                        $this->addToResponseArray($messages);
                    } else {
                        $this->addToResponse(false, 'goods-specification-unknown', null);
                    }
                }
            }
        }

        $this->db->commit();

        return $this->response($this->customResponse);
    }

    public function merchantInfoAction()
    {
        $organization_id = (int) $this->request->getPost("organization");
        $workplace_id = (int) $this->request->getPost("workplace");

        $current_merchant = Merchant::findFirst(
                        "organization = " . (int) $organization_id . " AND " .
                        "workplace = " . (int) $workplace_id
        );

        $tags = [];

        if ($current_merchant) {
            $merchant_shortlink = $current_merchant->shortlink;

            $merchant_tags = MerchantTag::find("merchant_id = " . (int) $current_merchant->id);

            foreach ($merchant_tags as $merchantTag) {
                $tag = Tag::findFirst((int) $merchantTag->tag_id);
                $tags[] = $tag->toArray();
            }
        } else {
            return $this->response(["error" => "merchant not exist"]);
        }

        return $this->response([
                    "id" => $current_merchant->id,
                    "shortlink" => $merchant_shortlink,
                    "tags" => $tags
        ]);
    }

    /**
     * Проверяет существует ли шортлинк исключая данный мерчант.
     */
    public function shortlinkExistInfoAction()
    {
        $organization_id_param = (int) $this->request->getPost("organization");
        $workplace_id_param = (int) $this->request->getPost("workplace");
        $is_network = (int) $this->request->getPost("is-org");
        $shortlink = $this->request->getPost("shortlink", "string");

        if ($is_network == 1) {
            $existed_organization = Organization::findFirst([
                "shortlink = :shortlink: AND id != :org_id:",
                "bind" => [
                    'shortlink' => $shortlink,
                    'org_id' => $organization_id_param,
                ],
            ]);

            $existed_merchant = Merchant::findFirst([
                        "shortlink = :shortlink:",
                        "bind" => [
                            'shortlink' => $shortlink,
                        ],
            ]);
        } else {
            $existed_organization = Organization::findFirst([
                        "shortlink = :shortlink:",
                        "bind" => [
                            'shortlink' => $shortlink,
                        ],
            ]);

            $existed_merchant = Merchant::findFirst([
                        "shortlink = :shortlink: AND (workplace != :workplace_id: OR workplace IS NULL) AND deleted IS NULL",
                        "bind" => [
                            'shortlink' => $shortlink,
                            'workplace_id' => $workplace_id_param,
                        ],
            ]);
        }

        return $this->response([
            "status" => true,
            "exist" => (bool) ($existed_organization || $existed_merchant),
        ]);
    }

    public function goodInfoAction()
    {
        $organization_id_param = (int) $this->request->getPost("organization");
        $good_article_param = (int) $this->request->getPost("article");

        $good = Good::findFirst(
                        "article = " . (int) $good_article_param . " AND " .
                        "organization = " . (int) $organization_id_param
        );

        if ($good) {
            return $this->response([
                        "url" => '/good/' . $good->id,
                        "deleted" => $good->deleted,
                        "sold" => $good->sold,
                        "withdrawn" => $good->withdrawn,
                        "counterAll" => $good->counter_all,
                        "counterPhone" => $good->counter_phone,
            ]);
        } else {
            return $this->response(["error" => "good not exist"]);
        }
    }

    public function needPhoneAction()
    {
        $good_id_param = (int) $this->request->getPost("good");

        $good = Good::findFirst((int) $good_id_param);

        if ($good) {
            $good->counter_phone++;
            $good->save();

            $merchant = $good->getMerchant();

            return $this->response([
                "status" => true,
                "phone" => $merchant->getPhone(),
            ]);
        } else {
            return $this->response([
                "status" => false,
                "message" => 'Товар не найден',
            ]);
        }
    }

    public function needMerchantPhoneAction()
    {
        $merchant_id_param = (int) $this->request->getPost("id");
        $merchant = Merchant::findFirst((int) $merchant_id_param);

        die($merchant->phone);
    }

    public function needAnswerAboutGoodAction()
    {
        $url = $this->getDI()->get('config')->smartlombard->url;
        $good_id_param = (int) $this->request->getPost("good");
        $good = Good::findFirst((int) $good_id_param);
        $merchant = $good->getMerchant();

        if ($merchant) {
            $organization = Organization::findFirst(intval($merchant->organization));

            if ($organization && !$organization->getSettings()['show_get_answer']) {
                return $this->response([
                    'error' => 'Операция запрещена'
                ]);
            }
        }

        if (empty($this->request->getPost('question_text')) || empty($this->request->getPost('contacts'))) {
            return $this->response([
                'error' => 'Заполните обязательные поля (помеченые звездочками)'
            ]);
        }

        $provider = Request::getProvider();
        $request_url = $url . '/api/store/request/answer-about-good';
        $provider->setBaseUri($request_url);
        $provider->header->set('Accept', '*/*');

        $filter = new Filter();

        $response = $provider->customPost($request_url, [
            'org' => $good->organization,
            'good' => $good->article,
            'name' => $filter->sanitize($this->request->getPost("name"), 'string'),
            'question_text' => $filter->sanitize($this->request->getPost("question_text"), 'string'),
            'contacts' => $filter->sanitize($this->request->getPost("contacts"), 'string')
        ]);

        $decoded_response = json_decode($response->body, true);

        if ($response->header->statusCode === 200 && json_last_error() === JSON_ERROR_NONE && $decoded_response['status'] === true) {
            return $this->response(['status' => true]);
        } else {
            return $this->response(['status' => false, 'message' => 'К сожалению во время выполнения вашего запроса произошла ошибка. Если ошибка повторяется обратитесь в техническую поддержку.']);
        }
    }

    public function needPriceAction()
    {
        $url = $this->getDI()->get('config')->smartlombard->url;

        if (empty($this->request->getPost("phone"))) {
            return $this->response([
                        'error' => 'Заполните обязательные поля (помеченые звездочками)'
            ]);
        }

        $good_id_param = (int) $this->request->getPost("good");
        $good = Good::findFirst((int) $good_id_param);

        $provider = Request::getProvider();
        $request_url = $url . '/api/store/request/price';
        $provider->setBaseUri($request_url);
        $provider->header->set('Accept', '*/*');

        $filter = new Filter();

        $response = $provider->customPost($request_url, [
            'org' => $good->organization,
            'good' => $good->article,
            'name' => $filter->sanitize($this->request->getPost("name"), 'string'),
            'phone' => $filter->sanitize($this->request->getPost("phone"), 'string'),
            'email' => $filter->sanitize($this->request->getPost("email"), 'string')
        ]);

        $decoded_response = json_decode($response->body, true);

        if ($response->header->statusCode === 200 && json_last_error() === JSON_ERROR_NONE && $decoded_response['status'] === true) {
            return $this->response(['status' => true]);
        } else {
            return $this->response(['status' => false, 'message' => 'К сожалению во время выполнения вашего запроса произошла ошибка. Если ошибка повторяется обратитесь в техническую поддержку.']);
        }
    }

    public function needCallAction()
    {
        $url = $this->getDI()->get('config')->smartlombard->url;
        $good_id_param = (int) $this->request->getPost("good");
        $good = Good::findFirst((int) $good_id_param);
        $merchant = $good->getMerchant();

        if ($merchant) {
            $organization = Organization::findFirst(intval($merchant->organization));

            if ($organization && !$organization->getSettings()['show_get_callback']) {
                return $this->response([
                    'error' => 'Операция запрещена'
                ]);
            }
        }

        if (empty($this->request->getPost('phone'))) {
            return $this->response([
                'error' => 'Заполните обязательные поля (помеченые звездочками)'
            ]);
        }

        $provider = Request::getProvider();
        $request_url = $url . '/api/store/request/call';
        $provider->setBaseUri($request_url);
        $provider->header->set('Accept', '*/*');

        $filter = new Filter();

        $response = $provider->customPost($request_url, [
            'org' => $good->organization,
            'good' => $good->article,
            'name' => $filter->sanitize($this->request->getPost("name"), 'string'),
            'phone' => $filter->sanitize($this->request->getPost("phone"), 'string')
        ]);

        $decoded_response = json_decode($response->body, true);

        if ($response->header->statusCode === 200 && json_last_error() === JSON_ERROR_NONE && $decoded_response['status'] === true) {
            return $this->response(['status' => true]);
        } else {
            return $this->response(['status' => false, 'message' => 'К сожалению во время выполнения вашего запроса произошла ошибка. Если ошибка повторяется обратитесь в техническую поддержку.']);
        }
    }

    public function needPhotoAction()
    {
        $url = $this->getDI()->get('config')->smartlombard->url;
        $good_id_param = (int) $this->request->getPost("good");
        $good = Good::findFirst((int) $good_id_param);
        $merchant = $good->getMerchant();

        if ($merchant) {
            $organization = Organization::findFirst(intval($merchant->organization));

            if ($organization && !$organization->getSettings()['show_get_photo']) {
                return $this->response([
                    'error' => 'Операция запрещена'
                ]);
            }
        }

        if (empty($this->request->getPost("email"))) {
            return $this->response([
                        'error' => 'Заполните обязательные поля (помеченые звездочками)'
            ]);
        }

        $provider = Request::getProvider();
        $request_url = $url . '/api/store/request/photo';
        $provider->setBaseUri($request_url);
        $provider->header->set('Accept', '*/*');

        $filter = new Filter();

        $response = $provider->customPost($request_url, [
            'org' => $good->organization,
            'good' => $good->article,
            'name' => $filter->sanitize($this->request->getPost("name"), 'string'),
            'email' => $filter->sanitize($this->request->getPost("email"), 'string')
        ]);

        $decoded_response = json_decode($response->body, true);

        if ($response->header->statusCode === 200 && json_last_error() === JSON_ERROR_NONE && $decoded_response['status'] === true) {
            return $this->response(['status' => true]);
        } else {
            return $this->response(['status' => false, 'message' => 'К сожалению во время выполнения вашего запроса произошла ошибка. Если ошибка повторяется обратитесь в техническую поддержку.']);
        }
    }

    public function getSearchDataAction()
    {
        $city_name_param = $this->getCityNameParam();
        $country_code_param = $this->getCountryCodeParam();
        $merchant_id_param = $this->request->getPost('merchant', 'int');
        // Допустимые значения all, other, /[\d]*/
        $category_id_param = $this->request->getPost('category_id', 'string');
        $category = GoodCategory::findFirst((int) $category_id_param);
        $id_jewelry = GoodCategory::getIdJewelry();
        $is_jewelry_subcategory = ($category_id_param == $id_jewelry || $category && $category->parent_id == $id_jewelry);
        // Допустимые значения all, /[\d]*/
        $phone_model_id_param = $this->request->getPost('phone_model_id', 'string');
        $only_with_price = $this->request->getPost('zero_price', 'int');
        $only_with_size = ($is_jewelry_subcategory ? $this->request->getPost('zero_size', 'int') : null);
        $metal_id_param = $this->request->getPost('metal', 'int');
        $metal_standart_id_param = $this->request->getPost('metal_standart', 'int');
        $min_price_filter = $this->request->getPost('min_price', 'int');
        $max_price_filter = $this->request->getPost('max_price', 'int');
        $min_size_filter = ($is_jewelry_subcategory ? $this->request->getPost('min_size', 'float') : null);
        $max_size_filter = ($is_jewelry_subcategory ? $this->request->getPost('max_size', 'float') : null);
        $search_text = $this->request->getPost('text', 'string');

        if ($phone_model_id_param && is_numeric($phone_model_id_param)) {
            // another hack for mobile category >:(
            $category_id_param = $phone_model_id_param;
        }

        $search_form = new SearchForm($this);

        if (!empty($city_name_param)) {
            $country = null;

            if ($city_name_param !== 'all') {
                $city = City::findFirst([
                            "name = :city_name: OR name_translit = :city_name:",
                            "bind" => ['city_name' => $city_name_param],
                ]);

                if ($city) {
                    $this->setCurrentCity($city);
                }
            } elseif ($country_code_param) {
                $country = Country::findFirst([
                            "code = :code:",
                            "bind" => ['code' => $country_code_param],
                ]);

                if ($country) {
                    $this->setCurrentCountry($country);
                }
            }
        }

        if (stripos($this->request->getPost('merchant', 'string'), 'network') !== false) {
            $merchant_network_id = $merchant_id_param;
            $this->setCurrentMerchantNetwork(Organization::findFirst((int) $merchant_network_id));
            $merchant_id_param = 0;
        } else {
            $merchant_network_id = 0;
        }

        if (!empty($merchant_id_param) && $city_name_param !== 'all') {
            $merchant = Merchant::findFirst((int) $merchant_id_param);

            if ($merchant) {
                $this->setCurrentMerchant($merchant);
            }
        }

        if (!empty($category_id_param)) {
            if ($category_id_param !== 'all' && $category_id_param !== 'other') {
                $category = GoodCategory::findFirst((int) $category_id_param);

                if ($category && $category->isSubCategory()) {
                    $subcategory = GoodSubCategory::findFirst((int) $category_id_param);

                    $this->setCurrentSubCategory($subcategory);
                } elseif ($category) {
                    $this->setCurrentCategory($category);
                }
            } elseif ($category_id_param === 'other') {
                $search_form->showCustomCategories();
            }
        }

        if (!empty($metal_id_param) && $metal_id_param != 'all') {
            $metal = GoodMetal::findFirst((int) $metal_id_param);

            if ($metal) {
                $this->setCurrentMetal($metal);
            }

            if (!empty($metal_standart_id_param) && $metal_standart_id_param != 'all') {
                $metal_standart = GoodMetalStandart::findFirst((int) $metal_standart_id_param);

                if ($metal_standart) {
                    $this->setCurrentMetalStandart($metal_standart);
                }
            }
        }

        if (is_numeric($min_price_filter)) {
            $this->view->filterPriceMin = $min_price_filter;
        }

        if (is_numeric($max_price_filter)) {
            $this->view->filterPriceMax = $max_price_filter;
        }

        if (!empty($only_with_price)) {
            $search_form->showOnlyWithPrice();
        } else {
            $search_form->showOnlyWithPrice(false);
        }

        if (is_numeric($min_size_filter)) {
            $this->view->filterSizeMin = $min_size_filter;
        }

        if (is_numeric($max_size_filter)) {
            $this->view->filterSizeMax = $max_size_filter;
        }

        if (!empty($only_with_size)) {
            $search_form->showOnlyWithSize();
        } else {
            $search_form->showOnlyWithSize(false);
        }

        if (!empty($search_text)) {
            $search_form->searchText($search_text);
        }

        $this->makeSearchForm($search_form);

        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        return $this->view->partial('partials/search_form');
    }

    private function response($output)
    {
        $this->response->setJsonContent($output);

        return $this->response;
    }

    public function addMerchantStep1Action()
    {
        $name = $this->request->getPost('name', 'string');
        $phone = $this->request->getPost('phone', 'string');
        $email = $this->request->getPost('email', 'email');

        $validation = new Validation();

        $validation->add(
                'name', new PresenceOf([
                    'message' => 'Укажите имя руководителя ломбарда.'
                        ])
        )->add(
                'name', new StringLength([
                    'max' => 100,
                    'min' => 2,
                    'messageMaximum' => 'Фио не может состоять более чем из 100 букв.',
                    'messageMinimum' => 'Фио не может состоять менее чем из двух букв.'
                        ])
        )->add(
                'phone', new PresenceOf([
                    'message' => 'Укажите контактный телефон руководителя.'
                        ])
        )->add(
                'phone', new StringLength([
                    'max' => 100,
                    'min' => 6,
                    'messageMaximum' => 'Номер телефона должен состоять максимум из 100 цифр.',
                    'messageMinimum' => 'Номер телефона должен состоять минимум из шести цифр.'
                        ])
        )->add(
                'email', new PresenceOf([
                    'message' => 'Укажите электронную почту руководителя ломбарда.'
                        ])
        )->add(
                'email', new Email([
                    'message' => 'Введите настоящий адрес электронной почты.'
                        ])
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            $errors = [];

            foreach ($messages as $message) {
                $errors[] = [
                    'field' => $message->getField(),
                    'message' => $message->getMessage()
                ];
            }

            return $this->response(['errors' => $errors]);
        } else {
            if ($this->request->getPost('submit', 'int') == 1) {
                $this->setCookieSameSite('add-merchant-name', $name, time() + 15 * 86400);
                $this->setCookieSameSite('add-merchant-phone', $phone, time() + 15 * 86400);
                $this->setCookieSameSite('add-merchant-email', $email, time() + 15 * 86400);
            }

            $city_name_param = $this->request->getPost('city', 'string');

            return $this->response(['success' => '/' . $city_name_param . '/add-merchant/step-2']);
        }
    }

    public function editMerchantStep1Action()
    {
        $name = $this->request->getPost('name', 'string');
        $phone = $this->request->getPost('phone', 'string');
        $email = $this->request->getPost('email', 'email');
        $id = $this->request->getPost('id', 'int');

        $validation = new Validation();

        $validation->add(
                'name', new PresenceOf([
                    'message' => 'Укажите имя руководителя ломбарда.'
                        ])
        )->add(
                'name', new StringLength([
                    'max' => 100,
                    'min' => 2,
                    'messageMaximum' => 'Фио не может состоять более чем из 100 букв.',
                    'messageMinimum' => 'Фио не может состоять менее чем из двух букв.'
                        ])
        )->add(
                'phone', new PresenceOf([
                    'message' => 'Укажите контактный телефон руководителя.'
                        ])
        )->add(
                'phone', new StringLength([
                    'max' => 100,
                    'min' => 6,
                    'messageMaximum' => 'Номер телефона должен состоять максимум из 100 цифр.',
                    'messageMinimum' => 'Номер телефона должен состоять минимум из шести цифр.'
                        ])
        )->add(
                'email', new PresenceOf([
                    'message' => 'Укажите электронную почту руководителя ломбарда.'
                        ])
        )->add(
                'email', new Email([
                    'message' => 'Введите настоящий адрес электронной почты.'
                        ])
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            $errors = [];

            foreach ($messages as $message) {
                $errors[] = [
                    'field' => $message->getField(),
                    'message' => $message->getMessage()
                ];
            }

            return $this->response(['errors' => $errors]);
        } else {
            if ($this->request->getPost('submit', 'int') == 1) {
                $this->setCookieSameSite('add-merchant-name', $name, time() + 15 * 86400);
                $this->setCookieSameSite('add-merchant-phone', $phone, time() + 15 * 86400);
                $this->setCookieSameSite('add-merchant-email', $email, time() + 15 * 86400);
            }

            $city_name_param = $this->request->getPost('city', 'string');

            return $this->response(['success' => '/' . $city_name_param . '/edit-merchant/' . $id . '/step-2']);
        }
    }

    public function addMerchantStep2Action()
    {
        $validation = new Validation();

        $validation->add(
                'name', new PresenceOf([
                    'message' => 'Укажите название ломбарда.'
                        ])
        )->add(
                'name', new StringLength([
                    'max' => 100,
                    'min' => 2,
                    'messageMaximum' => 'Название ломбарда не может состоять более чем из 100 букв.',
                    'messageMinimum' => 'Название ломбарда не может состоять менее чем из двух букв.'
                        ])
        )->add(
                'phone', new PresenceOf([
                    'message' => 'Укажите телефон по которому можно связаться с администрацией ломбарда.'
                        ])
        )->add(
                'phone', new StringLength([
                    'max' => 100,
                    'min' => 6,
                    'messageMaximum' => 'Номер телефона должен состоять максимум из 100 цифр.',
                    'messageMinimum' => 'Номер телефона должен состоять минимум из шести цифр.'
                        ])
        )->add(
                'adress', new PresenceOf([
                    'message' => 'Укажите адрес ломбарда.'
                        ])
        );

        $messages = $validation->validate($_POST);

        if (count($messages)) {
            $errors = [];

            foreach ($messages as $message) {
                $errors[] = [
                    'field' => $message->getField(),
                    'message' => $message->getMessage()
                ];
            }

            return $this->response(['errors' => $errors]);
        } else {
            if ($this->request->getPost('submit', 'int') == 1) {
                $merchant = new Merchant();
                $merchant->custom = 1;
                $merchant->new = 1;
                $merchant->deleted = 1;
                $merchant->city = trim($this->request->getPost('city'));
                $merchant->name = $this->request->getPost('name');
                $merchant->address = $this->request->getPost('adress');
                $merchant->phone = $this->request->getPost('phone');

                if (!empty($this->request->getPost('parent'))) {
                    $merchant->parent = (int) $this->request->getPost('parent');
                }

                if (!empty($this->request->getPost('site', 'string'))) {
                    $merchant->site = $this->request->getPost('site');
                }

                if (!empty($this->request->getPost('description', 'string'))) {
                    $merchant->description = $this->request->getPost('description');
                }

                if (!empty($this->request->getPost('working_hours', 'string'))) {
                    $merchant->working_hours = $this->request->getPost('working_hours');
                }

                if (!empty($this->request->getPost('filename', 'string'))) {
                    $merchant->logo = $this->request->getPost('filename');
                }

                if (!$merchant->create()) {
                    return $this->response([
                                'errors' => $merchant->getMessages()
                    ]);
                } else {
                    if (isset($merchant->logo) && !empty($merchant->logo)) {
                        $old_image_path = APP_PATH . '/public/img/merchant/unsorted/' . $merchant->logo;
                        $new_image_path = APP_PATH . '/public/img/merchant/' . $merchant->logo;

                        rename($old_image_path, $new_image_path);
                        unlink($old_image_path);
                    }

                    $tags_ids = $this->request->getPost('tags');

                    foreach ($tags_ids as $tag_id) {
                        $new_merchant_tag = new MerchantTag();

                        $new_merchant_tag->tag_id = (int) $tag_id;
                        $new_merchant_tag->merchant_id = $merchant->id;

                        $new_merchant_tag->create();
                    }
                }
            }

            return $this->response(true);
        }
    }

    public function addMerchantImageAction()
    {
        $merchant_images_path = APP_PATH . '/public/img/merchant/unsorted/';

        $allowed_image_extensions = [
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif'
        ];

        $errors = [];

        if (is_dir($merchant_images_path)) {
            if (!is_writable($merchant_images_path)) {
                $errors[] = 'Нет доступа для записи файла в папку: ' . $merchant_images_path;
            }
        } else {
            $create_path_result = mkdir($merchant_images_path, 0777, true);

            if (!$create_path_result) {
                $errors[] = 'Путь не существует. Не удалось создать путь: ' . $merchant_images_path;
            }
        }

        if ($this->request->hasFiles() == true) {
            foreach ($this->request->getUploadedFiles() as $file) {
                if ($file->getSize() >= 10485760) {
                    $errors[] = 'Размер файла превышает 10 МБ';
                }

                if (!isset($allowed_image_extensions[$file->getType()])) {
                    $errors[] = 'Неверный формат файла, разрешенные - jpg, png, gif.';
                }

                if (!count($errors)) {
                    $new_file_name = bin2hex(openssl_random_pseudo_bytes(12)) . $allowed_image_extensions[$file->getType()];

                    if ($file->moveTo($merchant_images_path . $new_file_name)) {
                        return $this->response([
                                    'success' => [
                                        'src' => '/img/merchant/unsorted/' . $new_file_name,
                                        'name' => $new_file_name,
                                    ]
                        ]);
                    } else {
                        $errors[] = 'Не удалось загрузить картинку. Проверте наличие папки unsorted, права доступа или наличие свободного места';

                        return $this->response(['errors' => $errors]);
                    }
                } else {
                    return $this->response(['errors' => $errors]);
                }
            }
        }
    }

    public function addStudioImageAction()
    {
        $merchant_studio_images_path = GoodStudioPhoto::getTemporaryUploadImagePath();

        $allowed_image_extensions = [
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif'
        ];

        $errors = [];

        if (is_dir($merchant_studio_images_path)) {
            if (!is_writable($merchant_studio_images_path)) {
                $errors[] = 'Нет доступа для записи файла в папку: ' . $merchant_studio_images_path;
            }
        } else {
            $create_path_result = mkdir($merchant_studio_images_path, 0777, true);

            if (!$create_path_result) {
                $errors[] = 'Путь не существует. Не удалось создать путь: ' . $merchant_studio_images_path;
            }
        }

        if ($this->request->hasFiles() == true) {
            foreach ($this->request->getUploadedFiles() as $file) {
                if ($file->getSize() >= 10485760) {
                    $errors[] = 'Размер файла превышает 10 МБ';
                }

                if (!isset($allowed_image_extensions[$file->getType()])) {
                    $errors[] = 'Неверный формат файла, разрешенные - jpg, png, gif.';
                }

                if (!count($errors)) {
                    $new_file_name = bin2hex(openssl_random_pseudo_bytes(12)) . $allowed_image_extensions[$file->getType()];

                    if ($file->moveTo($merchant_studio_images_path . $new_file_name)) {
                        return $this->response([
                                    'success' => [
                                        'src' => '/studio/_unsorted/' . $new_file_name,
                                        'name' => $new_file_name,
                                    ]
                        ]);
                    } else {
                        $errors[] = 'Не удалось загрузить картинку. Проверте наличие папки /public/studio/_unsorted/, права доступа или наличие свободного места';

                        return $this->response(['errors' => $errors]);
                    }
                } else {
                    return $this->response(['errors' => $errors]);
                }
            }
        }
    }

    public function merchantTagsAction()
    {
        $merchant_id_param = $this->request->getPost('merchant', 'int');

        $merchant_tags = MerchantTag::find("merchant_id = " . (int) $merchant_id_param);

        $tags = [];
        foreach ($merchant_tags as $merchant_tag) {
            $tag = Tag::findFirst((int) $merchant_tag->tag_id);
            $tags[] = $tag->toArray();
        }

        return $this->response(["merchantTags" => $tags]);
    }

    public function merchantPublicTagsAction()
    {
        $merchant_public_tags = Tag::find();

        if ($merchant_public_tags) {
            return $this->response(["publicTags" => $merchant_public_tags->toArray()]);
        } else {
            return $this->response(["publicTags" => []]);
        }
    }

    public function saveMerchantPublicTagsAction()
    {
        $new_merchant_tags_param = $this->request->getPost('tags');
        $merchant_id_param = $this->request->getPost('merchant', 'int');
        $old_merchant_tags = [];

        $merchant_tags = MerchantTag::find("merchant_id = " . (int) $merchant_id_param);
        foreach ($merchant_tags as $merchant_tag) {
            $old_merchant_tags[] = $merchant_tag->tag_id;
        }

        foreach ($new_merchant_tags_param as $new_tag_id) {
            if (!in_array($new_tag_id, $old_merchant_tags)) {
                $merchant_tag = MerchantTag::findFirst(
                                "merchant_id = " . (int) $merchant_id_param . " AND " .
                                "tag_id = " . (int) $new_tag_id
                );

                if (!$merchant_tag) {
                    $new_merchant_tag = new MerchantTag();
                    $new_merchant_tag->merchant_id = $merchant_id_param;
                    $new_merchant_tag->tag_id = $new_tag_id;
                    $new_merchant_tag->create();
                }
            }
        }

        foreach ($old_merchant_tags as $old_tag_id) {
            if (!in_array($old_tag_id, $new_merchant_tags_param)) {
                $merchant_tag = MerchantTag::findFirst(
                                "merchant_id = " . (int) $merchant_id_param . " AND " .
                                "tag_id = " . (int) $old_tag_id
                );

                if ($merchant_tag) {
                    $merchant_tag->delete();
                }
            }
        }

        return $this->response(['success' => 'Данные успешно сохранены.']);
    }

    public function addErrorReportAction()
    {
        if ($this->request->getPost('id')) {
            if ($this->request->getPost('merchant_close') != 'false' ||
                    $this->request->getPost('merchant_bad_adress') != 'false' ||
                    $this->request->getPost('merchant_phone_dntwork') != 'false' ||
                    $this->request->getPost('more') != '') {
                $errorReport = new ErrorReport();
                $errorReport->merchant_id = (int) $this->request->getPost('id');
                $errorReport->merchant_name = $this->request->getPost('merchant_name');
                $errorReport->merchant_close = (int) $this->request->getPost('merchant_close');
                $errorReport->merchant_bad_adress = (int) $this->request->getPost('merchant_bad_adress');
                $errorReport->merchant_phone_dntwork = (int) $this->request->getPost('merchant_phone_dntwork');
                $errorReport->more = (string) $this->request->getPost('more');
                $errorReport->create();
            } else {
                return $this->response(['errors' => 'Отсутсвуют данные']);
            }
        } else {
            return $this->response(['errors' => 'Отсутсвует id']);
        }
    }

    public function getStudioPhotoAction()
    {
        header('Access-Control-Allow-Origin: *');

        $studio_photo = GoodStudioPhoto::findStudioPhoto($this->dispatcher->getParam("goodName", 'string'));

        if ($studio_photo) {
            return $this->response(['imgUrl' => '/studio/' . $studio_photo->file_name]);
        }
    }

}
