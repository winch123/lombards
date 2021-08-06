<?php

use Phalcon\Http\Client\Request;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use PolombardamModels\City;
use PolombardamModels\Country;
use PolombardamModels\ErrorReport;
use PolombardamModels\GoodStudioPhoto;
use PolombardamModels\Merchant;
use PolombardamModels\MerchantTag;
use PolombardamModels\Page;
use PolombardamModels\Tag;

class AdminController extends Controller {

    public function initialize() {
        $this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
        $this->crumbs->add('home', '/admin/', 'Главная');
        $this->meta->setTitle("Polombardam : admin");
    }

    public function IndexAction() {
        $this->view->merchants_new = Merchant::find("new IS NOT NULL AND parent IS NULL");
        $this->view->merchants_edit = Merchant::find("new IS NOT NULL AND parent IS NOT NULL AND deleted IS NOT NULL");

        $this->view->merchants_errors = ErrorReport::findAllErrorReports(0);
    }

    public function citysAction() {
        $this->crumbs->add('citysAction', '/admin/citys/', 'Список городов', false);
    }

    public function citysAddAction() {
        $this->crumbs->add('citysAction', '/admin/citys/', 'Список городов');
        $this->crumbs->add('citysEditAction', '', 'Добавление города', false);

        $this->view->countries = Country::getAllSorted();
    }

    public function citysEditAction() {
        $city_id_param = (int) $this->dispatcher->getParam("id");

        $city = City::findFirst((int) $city_id_param);

        $this->crumbs->add('citysAction', '/admin/citys/', 'Список городов');
        $this->crumbs->add('citysEditAction', '', 'Редактирование: ' . $city->name, false);

        $this->view->city = $city;
        $this->view->countries = Country::getAllSorted();
    }

    public function studiosAction() {
        $this->crumbs->add('studiosAction', '/admin/studios/', 'Список студийных фото', false);
    }

    public function frequencyStudiosAction() {
        $this->crumbs->add('frequencyStudiosAction', '/admin/studios/', 'Список частоты запросов студийных фото', false);
    }

    public function merchantListAction() {
        $this->crumbs->add('merchantListAction', '/admin/merchants/', 'Список мерчантов', false);
    }

    public function merchantEditAction() {
        $marchant_id_param = (int) $this->dispatcher->getParam("id");

        $merchant = Merchant::findFirst((int) $marchant_id_param);

        $this->crumbs->add('merchantListAction', '/admin/merchants/', 'Список мерчантов');
        $this->crumbs->add('merchantEditAction', '', 'Редактирование мерчанта: ' . $merchant->name, false);

        $this->view->merchant = $merchant;

        $this->view->parents = [];

        $merchant_versions = Merchant::find(
                        (!empty($merchant->parent) ? 'id = ' . (int) $merchant->parent . ' OR ' : '') .
                        'parent = ' . (int) $merchant->id . " OR id = " . (int) $merchant->id .
                        'ORDER BY added DESC'
        );

        if ($merchant_versions) {
            $this->view->parents = $merchant_versions;
        }

        $merchant_tags = MerchantTag::find("merchant_id = " . (int) $merchant->id);

        if ($merchant_tags) {
            $marchant_tags_ids = [];

            foreach ($merchant_tags as $tag) {
                $marchant_tags_ids[] = (int) $tag->tag_id;
            }

            if (count($marchant_tags_ids)) {
                $tags = Tag::find("id IN (" . implode(',', $marchant_tags_ids) . ")");
                $this->view->tags = $tags;
            }
        }

        $all_tags = Tag::find();
        $this->view->allTags = $all_tags;
    }

    public function merchantErrorEditAction() {
        $error_report_id = (int) $this->dispatcher->getParam("id");

        $error_report = ErrorReport::findFirst((int) $error_report_id);

        if ($error_report->closed) {
            $this->crumbs->add('merchantListAction', '/admin/closedErrorReports/', 'Закрытые ошибки у мерчантов');
        }
        $this->crumbs->add('merchantEditAction', '', 'Ошибка у мерчанта ' . $error_report->merchant_name, false);

        $this->view->merchant = $error_report->getMerchant();
        $this->view->error = $error_report;
    }

    public function studiosEditAction() {
        $studio_photo_id = (int) $this->dispatcher->getParam("id");

        $studio_photo = GoodStudioPhoto::findFirst((int) $studio_photo_id);

        $this->crumbs->add('studiosListAction', '/admin/studios/', 'Список студийных фото');
        $this->crumbs->add('studiosEditAction', '', 'Редактирование: ' . $studio_photo->good_name, false);

        $this->view->studio = $studio_photo;
    }

    public function addFromFrequencyAction() {
        $name = $this->request->getQuery("name");

        $this->crumbs->add('studiosListAction', '/admin/studios/frequency', 'Список студийных фото');

        if ($name) {
            $cx = '016479350398671023892%3A8tfmoljggqu';
            $apikey = 'AIzaSyDvJSfKPhLAAcJ7oAEiWhTAEtTK8Wsk1Qc';

            $this->crumbs->add('studiosEditAction', '', 'Добавление изображения для: ' . $name, false);

            $provider = Request::getProvider();
            $provider->setBaseUri('https://www.googleapis.com/customsearch/');
            $provider->header->set('Accept', '*/*');
            $response = $provider->get('v1?q=' . $name . '&cx=' . $cx . '&key=' . $apikey . '&num=10&fields=items%2Fpagemap');
            $response = json_decode($response->body, true);

            $images = [];
            if (count($response['items'])) {
                foreach ($response['items'] as $item) {
                    $images[] = $item['pagemap']['cse_image'][0]['src'];
                }
            }

            $this->view->googleImages = $images;
            $this->view->good_name = $name;
        } else {
            $this->crumbs->add('studiosEditAction', '', 'Добавление изображения', false);
        }
    }

    public function removeFromStudiosAction() {
        $this->view->disable();
        $studio_photo_id = (int) $this->dispatcher->getParam("id");

        $request = GoodStudioPhoto::findFirst((int) $studio_photo_id);

        if ($request) {
            if ($request->delete()) {
                return $this->response(['success' => 'Успешно удалено']);
            } else {
                return $this->response(['errors' => $request->getMessages()]);
            }
        }
    }

    private function response($output) {
        $this->response->setJsonContent($output);
        return $this->response;
    }

    public function pagesAction() {
        $this->crumbs->add('pagesAction', '/admin/pages/', 'Список страниц', false);

        $pages = Page::find("controller = 'static' OR controller = 'index'");
        $sortedPages = [];

        // Собираем первый уровень меню.
        foreach ($pages as $page) {
            if ($page->parent == 'index') {
                $sortedPages[$page->controller . '/' . $page->action] = [
                    'index' => $page
                ];
            } else if ($page->parent == null) {
                $sortedPages[$page->controller . '/' . $page->action] = [
                    'index' => $page
                ];
            }
        }

        foreach ($pages as $page) {
            foreach ($sortedPages as $key => $sorted) {
                if (($page->parent . '/index') == $key && 'index/index' != $key) {
                    $sortedPages[$key]['pages'][] = $page;
                }
            }
        }

        $this->view->pages = $sortedPages;
    }

    public function pagesEditAction() {
        $this->crumbs->add('pagesAction', '/admin/pages/', 'Список страниц');
        $this->crumbs->add('pagesEditAction', '', 'Редактирование', false);

        $page_id = (int) $this->dispatcher->getParam("id");

        $page = Page::findFirst((int) $page_id);

        $this->view->page = $page;
    }

    public function pagesAddAction() {
        $this->crumbs->add('pagesAction', '/admin/pages/', 'Список страниц');
        $this->crumbs->add('pagesEditAction', '', 'Добавление', false);
    }

    public function tagsAction() {
        $this->crumbs->add('tagsAction', '', 'Список тегов', false);
    }

    public function tagsEditAction() {
        $this->crumbs->add('tagsAction', '/admin/tags/', 'Список тегов');
        $this->crumbs->add('tagsEditAction', '', 'Редактирование', false);

        $tag_id = (int) $this->dispatcher->getParam("id");

        $tag = Tag::findFirst((int) $tag_id);

        $this->view->merchantTag = $tag;
    }

    public function tagsAddAction() {
        $this->crumbs->add('tagsAction', '/admin/tags/', 'Список тегов');
        $this->crumbs->add('tagsAddAction', '', 'Добавление', false);
    }

    public function closedErrorReportsAction() {
        $this->crumbs->add('merchantEditAction', '', 'Закрытые ошибки у мерчантов', false);
        $this->view->closed_merchants_errors = ErrorReport::findAllErrorReports(1);
    }

}
