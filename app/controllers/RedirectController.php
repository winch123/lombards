<?php

use Phalcon\Http\Response;
use Phalcon\Http\Response\StatusCode;
use Phalcon\Mvc\Controller;
use PolombardamModels\City;
use PolombardamModels\Merchant;

class RedirectController extends Controller {

    public function initialize() {

    }

    public function indexAction() {

    }

    public function cityAction() {
        $city_name = $this->dispatcher->getParam("city", 'string');

        $city = City::findFirst([
                    "old_url = :old_url:",
                    'bind' => ['old_url' => $city_name],
        ]);

        if ($city) {
            $response = new Response();
            $response->redirect("/city/" . $city->name, true, StatusCode::FOUND);
            return $response;
        } else {
            $response = new Response();
            $response->redirect("/route404", true, StatusCode::FOUND);
            return $response;
        }
    }

    public function merchantListAction() {
        $city_name = $this->dispatcher->getParam("city", 'string');

        $city = City::findFirst([
                    "old_url = :old_url:",
                    'bind' => ['old_url' => $city_name],
        ]);

        if ($city) {
            $response = new Response();
            $response->redirect("/" . $city->name . "/spisok_lombardov", true, StatusCode::FOUND);
            return $response;
        } else {
            $response = new Response();
            $response->redirect("/route404", true, StatusCode::FOUND);
            return $response;
        }
    }

    public function merchantAction() {
        if (empty($this->dispatcher->getParam("merchant"))) {
            return $this->merchantListAction();
        }

        if (empty($this->dispatcher->getParam("merchant1"))) {
            return $this->cityAction();
        }

        $city_name = $this->dispatcher->getParam("city", 'string');
        $merchant_name = $this->dispatcher->getParam("merchant", 'string');

        $city = City::findFirst([
                    "old_url = :old_url:",
                    'bind' => ['old_url' => $city_name],
        ]);

        if (!$city) {
            $response = new Response();
            $response->redirect("/route404", true, StatusCode::FOUND);
            return $response;
        }

        $merchant = Merchant::findFirst([
                    "old_url = :old_url: AND city = :city_name:",
                    'bind' => [
                        'old_url' => $merchant_name,
                        'city_name' => $city->name
                    ],
        ]);

        if ($merchant) {
            $response = new Response();
            $response->redirect($merchant->getUrl(), true, StatusCode::FOUND);
            return $response;
        } else {
            $response = new Response();
            $response->redirect("/route404", true, StatusCode::FOUND);
            return $response;
        }
    }

}
