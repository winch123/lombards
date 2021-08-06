<?php

use Phalcon\Mvc\Controller;
use Polombardam\StringsHelper;
use PolombardamModels\GoodCategory;
use PolombardamModels\GoodMetal;
use PolombardamModels\GoodMetalStandart;
use PolombardamModels\GoodMetalStandartsRelations;
use PolombardamModels\GoodSubCategory;
use PolombardamModels\Merchant;
use PolombardamModels\Organization;
use PolombardamModels\Partner;

class ApiadminController extends Controller {

    public function initialize() {
        $this->view->disable();
        $this->response->setContentType('application/json', 'UTF-8');

        if (!$this->authCheck() || !$this->authAdminCheck()) {
            $this->responseApiError(401, 'Ошибка авторизации');
        }
    }

    private function responseApiError($statusCode, $errorMessage = '') {
        $this->response->setStatusCode($statusCode);

        $this->responseJson([
            "status" => false,
            "error" => $errorMessage,
        ]);
    }

    private function responseApiSuccess($statusCode = 200, $successMessage = '') {
        $this->response->setStatusCode($statusCode);

        $this->responseJson([
            "status" => true,
            "message" => $successMessage,
        ]);
    }

    private function responseJson($output) {
        $this->response->setJsonContent($output);
        $this->response->send();
        die;
    }

    /**
     *
     * @return mixed
     */
    private function getRawPostData() {
        return $this->request->getPost();
    }

    /**
     *
     * @return mixed
     */
    private function getPostData() {
        $post = $this->request->getPost();

        if (count($post) === 1 && isset($post['_post_json'])) {
            return json_decode($post['_post_json'], true);
        } else {
            return $post;
        }
    }

    /**
     *
     * @return int
     */
    private function getPartnerId() {
        return (int) $this->request->getHeader("PartnerID");
    }

    private function authCheck() {
        $partner_id = $this->getPartnerId();
        $user_sign = $this->request->getHeader("Sign");

        if (!empty($user_sign) && !empty($partner_id)) {
            $partner = Partner::findFirst((int) $partner_id);

            if ($partner) {
                $sign = sha1(sha1(serialize($this->getRawPostData())) . $partner->hash);

                if ($user_sign === $sign) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * @return boolean
     */
    private function authAdminCheck() {
        $user_admin_sign = $this->request->getHeader("AdminSign");
        $system_admin_api_key = $this->getDI()->get('config')['smartlombard']['api_admin_key'];

        if (empty($system_admin_api_key)) {
            $this->responseApiError(500, 'Admin api key is not configured!');

            return false;
        }

        if (!empty($user_admin_sign)) {
            $sign = sha1(sha1(serialize($this->getRawPostData())) . $system_admin_api_key);

            if ($user_admin_sign === $sign) {
                return true;
            }
        }

        return false;
    }

    public function specificationsUpdateAction() {
        $partner_id = $this->getPartnerId();
        $params = $this->getPostData();

        $connection = $this->db;

        // start transaction
        $connection->begin();

        $connection->execute("DELETE FROM `good_specs` WHERE partner_id = ? AND org_id = ?", [$partner_id, $params['org_id']]);

        foreach ($params['specifications'] as $specification_data) {
            $insert_data = [
                $partner_id,
                $params['org_id'],
                $specification_data['name'],
                $specification_data['specification'],
            ];

            $connection->execute("INSERT INTO `good_specs` (`partner_id`, `org_id`, `good_name`, `specification`) VALUES (?, ?, ?, ?)", $insert_data);
        }

        /*
         * Если использовать ID придется при обновлении товара проверять его
         * спецификацию, ровно как и при обновлении спецификаций.
         * А вот если использовать внешние ключи в виде name+organization вроде
         * проблема отпадает, но это подходит только для малого кол-ва данных
         * (например при отображении только 1 товара, на странице товара).
         */
//        $phql_update = "UPDATE `good` g "
//                . "INNER JOIN `good_specs` gs ON g.name = gs.good_name "
//                . "SET g.specification_id = gs.id "
//                . "WHERE g.deleted IS NULL "
//                . "AND gs.specification != ''";
//        $connection->execute($phql_update);

        $connection->commit();

        $this->responseApiSuccess(200, 'Спецификации товаров успешно заменены');
    }

    /**
     *
     * @param string $category_name
     * @param string $parent_category_name
     * @param GoodCategory[] $categories_array
     * @return GoodCategory
     */
    private static function getCategoryFromArrayByName($category_name, $parent_category_name, $categories_array) {
        foreach ($categories_array as $category) {
            if ($category->name === $category_name) {
                if ($parent_category_name === null && $category->parent_id == 0) {
                    return $category;
                } elseif ($parent_category_name !== null) {
                    $parent_cat = GoodCategory::findFirst((int) $category->parent_id);

                    if ($parent_cat && $parent_cat->name === $parent_category_name) {
                        return $category;
                    }
                }
            }
        }
    }

    public function categoriesUpdateAction() {
        $connection = $this->db;

        // start transaction
        $connection->begin();

        $connection->execute("UPDATE `good_category` SET `system` = 0, `sort` = 0 WHERE `system` = 1 OR `sort` > 0");

        $system_categories_array = $this->getPostData();
        // convert resultset to a simple objects array
        $categories = iterator_to_array(GoodCategory::find());

        foreach ($system_categories_array as $system_category) {
            $category = self::getCategoryFromArrayByName($system_category['name'], $system_category['parent_name'], $categories);

            if (!$category) {
                $category = new GoodCategory();
                $category->name = $system_category['name'];
                $category->name_translit = StringsHelper::translitRusStringToUrl($category->name);

                if (!$category->create()) {
                    $connection->rollback();

                    $this->responseApiError(200, 'Произошла ошибка при создании категории: ' . $category->name);
                }

                $categories[] = $category;
            }

            if ($system_category['parent_id']) {
                $parent_category = self::getCategoryFromArrayByName($system_category['parent_name'], null, $categories);

                if (!$parent_category) {
                    $parent_category = new GoodCategory();
                    $parent_category->name = $system_category['parent_name'];
                    $parent_category->name_translit = StringsHelper::translitRusStringToUrl($parent_category->name);

                    if (!$parent_category->create()) {
                        $connection->rollback();

                        $this->responseApiError(200, 'Произошла ошибка при создании родительской категории: ' . $parent_category->name);
                    }

                    $categories[] = $parent_category;
                }

                $category->parent_id = $parent_category->id;
            } else {
                // родительская
                $category->parent_id = 0;
            }

            $category->sort = $system_category['sort'];
            $category->system = 1;

            if (!$category->save()) {
                $connection->rollback();

                $this->responseApiError(200, 'Произошла ошибка при сохранении категории: ' . $category->name);
            }
        }

        $connection->commit();

        $this->responseApiSuccess(200, 'Системные категории товаров успешно обновлены');
    }

    /**
     * Создание системной категории
     */
    public function categoryCreateAction() {
        $post_data = $this->getPostData();
        $category_name = $post_data['category_name'];
        $parent_category_name = $post_data['parent_category_name'];

        if ($parent_category_name) {
            $parent_category = GoodCategory::getCategoryByName($parent_category_name, true);

            if (!$parent_category) {
                $this->responseApiError(200, 'Произошла ошибка при создании категории, не найдена старшая категория: ' . $parent_category_name);
            } else {
                $category = GoodSubCategory::getSubCategoryByName($category_name, $parent_category_name);
            }
        } else {
            $parent_category = null;
            $category = GoodCategory::getCategoryByName($category_name);
        }

        if ($category) {
            $category->system = 1;

            if (!$category->save()) {
                $this->responseApiError(200, 'Произошла ошибка при редактировании категории: ' . $category->name);
            }
        } else {
            if ($parent_category) {
                $category = new GoodSubCategory();
                $category->parent_id = $parent_category->getId();
            } else {
                $category = new GoodCategory();
                $category->parent_id = 0;
            }

            $category->name = $category_name;
            $category->name_translit = StringsHelper::translitRusStringToUrl($category->name);
            $category->system = 1;

            if (!$category->create()) {
                $this->responseApiError(200, 'Произошла ошибка при создании категории: ' . $category->name);
            }
        }

        $this->responseApiSuccess(200, 'Категория успешно создана');
    }

    /**
     * Переименование системной категории
     */
    public function categoryRenameAction() {
        $post_data = $this->getPostData();

        if ($post_data['parent_name']) {
            $category = GoodSubCategory::getSubCategoryByName($post_data['old_name'], $post_data['parent_name'], true);
        } else {
            $category = GoodCategory::getCategoryByName($post_data['old_name'], true);
        }

        if ($category) {
            $category->name = $post_data['new_name'];
            $category->name_translit = StringsHelper::translitRusStringToUrl($category->name);

            if (!$category->save()) {
                $this->responseApiError(200, 'Произошла ошибка при переименовании категории: ' . $category->name);
            }
        } else {
            $this->responseApiError(200, 'Не найдена категория  именем: ' . ($post_data['parent_name'] ? $post_data['parent_name'] . '/' : '') . $category->name);
        }

        $this->responseApiSuccess(200, 'Системныя категория успешно переименована');
    }

    /**
     * Удаление системной категории
     */
    public function categoryRemoveAction() {
        $post_data = $this->getPostData();

        if ($post_data['parent_category_name']) {
            $category = GoodSubCategory::getSubCategoryByName($post_data['category_name'], $post_data['parent_category_name'], true);
        } else {
            $category = GoodCategory::getCategoryByName($post_data['category_name'], true);
        }

        if ($category) {
            $category->system = 0;

            if (!$category->save()) {
                $this->responseApiError(200, 'Произошла ошибка при редактировании категории: ' . $category->name);
            }
        }

        $this->responseApiSuccess(200, 'Категория успешно удалена');
    }

    public function metalStandartsUpdateAction() {
        $connection = $this->db;

        // start transaction
        $connection->begin();

        $connection->execute("UPDATE `good_metal` SET `system` = 0 WHERE `system` = 1");
        $connection->execute("UPDATE `good_metal_standarts` SET `system` = 0 WHERE `system` = 1");

        $system_metal_standarts = $this->getPostData();

        foreach ($system_metal_standarts as $system_metal_standart) {
            // проба металла
            $metal_standart_name = $system_metal_standart['name'];
            $metal_standart = GoodMetalStandart::findFirst([
                        'name = :name:',
                        'bind' => [
                            'name' => $metal_standart_name,
                        ],
            ]);

            if (!$metal_standart) {
                $metal_standart = new GoodMetalStandart();
                $metal_standart->name = $metal_standart_name;
                $metal_standart->system = 1;

                if (!$metal_standart->create()) {
                    $connection->rollback();

                    $this->responseApiError(200, 'Произошла ошибка при создании пробы: ' . $metal_standart->name);
                }
            } else {
                $metal_standart->system = 1;

                if (!$metal_standart->save()) {
                    $connection->rollback();

                    $this->responseApiError(200, 'Произошла ошибка при обновлении пробы: ' . $metal_standart->name);
                }
            }

            // металл
            $metal_name = $system_metal_standart['metal_name'];
            $metal = GoodMetal::findFirst([
                        'name = :name:',
                        'bind' => [
                            'name' => $metal_name,
                        ],
            ]);

            if (!$metal) {
                $metal = new GoodMetal();
                $metal->name = $metal_name;
                $metal->system = 1;

                if (!$metal->create()) {
                    $connection->rollback();

                    $this->responseApiError(200, 'Произошла ошибка при создании металла: ' . $metal_standart->name);
                }
            } else {
                $metal->system = 1;

                if (!$metal->save()) {
                    $connection->rollback();

                    $this->responseApiError(200, 'Произошла ошибка при обновлении металла: ' . $metal_standart->name);
                }
            }

            // связи
            $metal_standart_relation = GoodMetalStandartsRelations::findFirst([
                        'metal_standart_id = :metal_standart_id:',
                        'bind' => [
                            'metal_standart_id' => $metal_standart->id,
                        ],
            ]);

            if (!$metal_standart_relation) {
                $metal_standart_relation = new GoodMetalStandartsRelations ();
                $metal_standart_relation->metal_standart_id = $metal_standart->id;
                $metal_standart_relation->metal_id = $metal->id;

                if (!$metal_standart_relation->create()) {
                    $connection->rollback();

                    $this->responseApiError(200, 'Произошла ошибка при создании связи пробы и металла: ' . $metal_standart->name);
                }
            } else {
                $metal_standart_relation->metal_id = $metal->id;

                if (!$metal_standart_relation->save()) {
                    $connection->rollback();

                    $this->responseApiError(200, 'Произошла ошибка при обновлении связи пробы и металла: ' . $metal_standart->name);
                }
            }
        }

        $connection->commit();

        $this->responseApiSuccess(200, 'Системные пробы и металлы товаров успешно обновлены');
    }

    public function shortlinkGetInfoAction() {
        $shortlink = $this->dispatcher->getParam('shortlink', 'string');

        $merchant = Merchant::findFirst([
                    'shortlink = :shortlink:',
                    'bind' => [
                        'shortlink' => $shortlink,
                    ],
        ]);

        $found_result = [];
        if ($merchant) {
            $found_result[] = [
                'merchant_network_id' => $merchant->organization,
                'merchant_id' => $merchant->id,
            ];
        } else {
            $merchant_network = Organization::findFirst([
                        'shortlink = :shortlink:',
                        'bind' => [
                            'shortlink' => $shortlink,
                        ],
            ]);

            if ($merchant_network) {
                $found_result[] = [
                    'merchant_network_id' => $merchant_network->id,
                    'merchant_id' => 0,
                ];
            }
        }

        $this->responseApiSuccess(200, $found_result);
    }

}
