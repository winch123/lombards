<?php

use Phalcon\Mvc\Router;

$router = new Router(false);

$router->removeExtraSlashes(true);

$router->add(
    "#^/([a-zA-Zа-яА-Я0-9_\/]+)$#u",
    array(
        "controller" => "merchant",
        "params" => 0
    )
);

$router->add(
    "/",
    array(
        "controller" => "index"
    )
);

$router->add(
    "/choose_city/{countryCode}",
    array(
        "controller" => "index",
        "action" => "chooseCity",
    )
);

$router->add(
    "/merchant/{merchantId}",
    array(
        "controller" => "merchant"
    )
);

$router->add(
    "/merchant/{merchantId}/{category}",
    array(
        "controller" => "merchant"
    )
);

$router->add(
    "/merchant/{merchantId}/{category}/{subcategory}",
    array(
        "controller" => "merchant"
    )
);

$router->add(
    "/merchant/add[/]?{cityName:.*}",
    array(
        "controller" => "merchant",
        "action" => "add",
    )
);

$router->add(
    "/network/{id}",
    array(
        "controller" => "merchantnetwork"
    )
);

$router->add(
    "/{city}/network/{id}",
    array(
        "controller" => "merchantnetwork"
    )
);

$router->add(
    "/search",
    array(
        "controller" => "search"
    )
);

$router->add(
    "/{cityName}/spisok_lombardov",
    array(
        "controller" => "merchant",
        "action" => "all"
    )
);

$router->add(
    "/{cityName}/tag/{tagId}",
    array(
        "controller" => "merchant",
        "action" => "all",
    )
);

$router->add(
    "/category/{category}",
    array(
        "controller" => "category"
    )
);

$router->add(
    "/category/{category}/{subcategory}",
    array(
        "controller" => "category"
    )
);

$router->add(
    "/city/{cityName}",
    array(
        "controller" => "city",
        "action"     => "show"
    )
);

$router->add(
    "/city/remember",
    array(
        "controller" => "city",
        "action" => "remember",
    )
);

$router->add(
    "/city/{cityName}/{category}",
    array(
        "controller" => "city",
        "action"     => "show"
    )
);

//$router->add(
//    "/ajax/city/{cityName}/{category}",
//    array(
//        "controller" => "city",
//        "action"     => "ajaxshow"
//    )
//);

$router->add(
    "/city/{cityName}/{category}/{subcategory}",
    array(
        "controller" => "city",
        "action"     => "show"
    )
);

//$router->add(
//    "/ajax/city/{cityName}/{category}/{subcategory}",
//    array(
//        "controller" => "city",
//        "action"     => "ajaxshow"
//    )
//);

$router->add(
    "/city/{cityName}/good",
    array(
        "controller" => "good"
    )
);

$router->add(
    "/{cityName}/good/{goodId}",
    array(
        "controller" => "good",
        "action"     => "showGood"
    )
);

$router->add(
    "/{cityName}/add-merchant",
    array(
        "controller" => "merchant",
        "action"     => "add"
    )
);

$router->add(
    "/{cityName}/add-merchant/step-2",
    array(
        "controller" => "merchant",
        "action"     => "add2"
    )
);

$router->add(
    "/{cityName}/edit-merchant/{id}",
    array(
        "controller" => "merchant",
        "action"     => "edit"
    )
);

$router->add(
    "/{cityName}/edit-merchant/{id}/step-2",
    array(
        "controller" => "merchant",
        "action"     => "edit2"
    )
);

$router->add(
    "/{cityName}/edit-merchant/{id}/step-3",
    array(
        "controller" => "merchant",
        "action"     => "edit3"
    )
);


$router->add(
    "/good/{goodId}",
    array(
        "controller" => "good",
        "action"     => "showGood"
    )
);

$router->add(
    "/api/settings/save",
    array(
        "controller" => "api",
        "action"     => "saveSettings"
    )
);

$router->add(
    "/api/push-message",
    array(
        "controller" => "api"
    )
);

$router->add(
    "/api/merchant-info",
    array(
        "controller" => "api",
        "action"     => "merchantInfo"
    )
);

$router->add(
    "/api/merchant-tags",
    array(
        "controller" => "api",
        "action"     => "merchantTags"
    )
);

$router->add(
    "/api/merchant-public-tags",
    array(
        "controller" => "api",
        "action"     => "merchantPublicTags"
    )
);

$router->add(
    "/api/save-merchant-public-tags",
    array(
        "controller" => "api",
        "action"     => "saveMerchantPublicTags"
    )
);

$router->add(
    "/api/add-merchant-public-tag",
    array(
        "controller" => "api",
        "action"     => "addMerchantPublicTag"
    )
);

$router->add(
    "/api/delete-merchant-public-tag",
    array(
        "controller" => "api",
        "action"     => "deleteMerchantPublicTag"
    )
);

$router->add(
    "/api/good-info",
    array(
        "controller" => "api",
        "action"     => "goodInfo"
    )
);

$router->add(
    "/api/shortlink-exist-info",
    array(
        "controller" => "api",
        "action"     => "shortlinkExistInfo"
    )
);

$router->add(
    "/api/admin/specifications/update",
    array(
        "controller" => "apiadmin",
        "action"     => "specificationsUpdate"
    )
);

$router->add(
    "/api/admin/categories/update",
    array(
        "controller" => "apiadmin",
        "action"     => "categoriesUpdate"
    )
);

$router->add(
    "/api/admin/category/create",
    array(
        "controller" => "apiadmin",
        "action"     => "categoryCreate"
    )
);

$router->add(
    "/api/admin/category/rename",
    array(
        "controller" => "apiadmin",
        "action"     => "categoryRename"
    )
);

$router->add(
    "/api/admin/category/remove",
    array(
        "controller" => "apiadmin",
        "action"     => "categoryRemove"
    )
);

$router->add(
    "/api/admin/metal_standarts/update",
    array(
        "controller" => "apiadmin",
        "action"     => "metalStandartsUpdate"
    )
);

$router->add(
    "/api/admin/shortlink/{shortlink}/get",
    array(
        "controller" => "apiadmin",
        "action"     => "shortlinkGetInfo"
    )
);

$router->add(
    "/article/{page}",
    array(
        "controller" => "static",
        "action"     => "page"
    )
);

$router->add(
    "/article",
    array(
        "controller" => "static",
        "action"     => "index"
    )
);

$router->add(
    "/api/need-phone",
    array(
        "controller" => "api",
        "action"     => "needPhone"
    )
);

$router->add(
    "/api/need-merchant-phone",
    array(
        "controller" => "api",
        "action"     => "needMerchantPhone"
    )
);

$router->add(
    "/api/need-answer-about-good",
    array(
        "controller" => "api",
        "action"     => "needAnswerAboutGood"
    )
);

$router->add(
    "/api/need-photo",
    array(
        "controller" => "api",
        "action"     => "needPhoto"
    )
);

$router->add(
    "/api/need-price",
    array(
        "controller" => "api",
        "action"     => "needPrice"
    )
);

$router->add(
    "/api/need-call",
    array(
        "controller" => "api",
        "action"     => "needCall"
    )
);

$router->add(
    "/api/get-search-data",
    array(
        "controller" => "api",
        "action"     => "getSearchData"
    )
);

$router->add(
    "/api/edit-merchant-step-1",
    array(
        "controller" => "api",
        "action"     => "editMerchantStep1"
    )
);

$router->add(
    "/api/edit-merchant-step-2",
    array(
        "controller" => "api",
        "action"     => "editMerchantStep2"
    )
);

$router->add(
    "/api/edit-merchant-step-3",
    array(
        "controller" => "api",
        "action"     => "editMerchantStep3"
    )
);

$router->add(
    "/api/add-merchant-step-1",
    array(
        "controller" => "api",
        "action"     => "addMerchantStep1"
    )
);

$router->add(
    "/api/add-merchant-step-2",
    array(
        "controller" => "api",
        "action"     => "addMerchantStep2"
    )
);

$router->add(
    "/api/add-merchant-image",
    array(
        "controller" => "api",
        "action"     => "addMerchantImage"
    )
);

$router->add(
    "/api/add-studio-image",
    array(
        "controller" => "api",
        "action"     => "addStudioImage"
    )
);

$router->add(
    "/api/add-error-report",
    array(
        "controller" => "api",
        "action"     => "addErrorReport"
    )
);

$router->add(
    "/api/get-studio-photo/{goodName}",
    array(
        "controller" => "api",
        "action"     => "getStudioPhoto"
    )
);

/**
 * Эти роуты для админки
 */
$router->add(
    '/admin/api/:action', [
        'controller' => 'adminapi',
        'action'     => 1
    ]
);

$router->add(
    '/admin', [
        'controller' => 'admin'
    ]
);

$router->add(
    '/admin/merchants', [
        'controller' => 'admin',
        'action' => 'merchantList'
    ]
);

$router->add(
    '/admin/merchants/add', [
        'controller' => 'admin',
        'action' => 'merchantAdd'
    ]
);

$router->add(
    '/admin/merchants/edit{id}', [
        'controller' => 'admin',
        'action' => 'merchantEdit'
    ]
);

$router->add(
    '/admin/merchants_error/edit{id}', [
        'controller' => 'admin',
        'action' => 'merchantErrorEdit'
    ]
);

$router->add(
    '/admin/closedErrorReports', [
        'controller' => 'admin',
        'action' => 'closedErrorReports'
    ]
);

$router->add(
    '/admin/pages', [
        'controller' => 'admin',
        'action' => 'pages'
    ]
);

$router->add(
    '/admin/pages/edit{id}', [
        'controller' => 'admin',
        'action' => 'pagesEdit'
    ]
);

$router->add(
    '/admin/pages/add', [
        'controller' => 'admin',
        'action' => 'pagesAdd'
    ]
);

$router->add(
    '/admin/citys', [
        'controller' => 'admin',
        'action' => 'citys'
    ]
);

$router->add(
    '/admin/citys/edit{id}', [
        'controller' => 'admin',
        'action' => 'citysEdit'
    ]
);

$router->add(
    '/admin/citys/add', [
        'controller' => 'admin',
        'action' => 'citysAdd'
    ]
);

$router->add(
    '/admin/tags', [
        'controller' => 'admin',
        'action' => 'tags'
    ]
);
$router->add(
    '/admin/tags/add', [
        'controller' => 'admin',
        'action' => 'tagsAdd'
    ]
);

$router->add(
    '/admin/tags/edit{id}', [
        'controller' => 'admin',
        'action' => 'tagsEdit'
    ]
);

$router->add(
    '/admin/studios', [
        'controller' => 'admin',
        'action' => 'studios'
    ]
);
$router->add(
    '/admin/studios/edit{id}', [
        'controller' => 'admin',
        'action' => 'studiosEdit'
    ]
);

$router->add(
    '/admin/studios/frequency', [
        'controller' => 'admin',
        'action' => 'frequencyStudios'
    ]
);

$router->add(
    '/admin/studios/addFromFrequency', [
        'controller' => 'admin',
        'action' => 'addFromFrequency'
    ]
);

$router->add(
    '/admin/studios/removeFromStudios{id}', [
        'controller' => 'admin',
        'action' => 'removeFromStudios'
    ]
);
/**
 * Эти роуты для редиректов
 */
$router->add(
    "/main/{city}",
    array(
        "controller" => "redirect",
        "action" => "city"
    )
);

$router->add(
    "/main/{city}/spisok_lombardov",
    array(
        "controller" => "redirect",
        "action" => "merchantList"
    )
);

$router->add(
    "/main/{city}/spisok_lombardov/{merchant}",
    array(
        "controller" => "redirect",
        "action" => "merchant"
    )
);

$router->add(
    "/main/{city}/{merchant1}/{merchant}",
    array(
        "controller" => "redirect",
        "action" => "merchant"
    )
);

$router->add(
    "/main/{city}/{merchantList}",
    array(
        "controller" => "redirect",
        "action" => "merchantList"
    )
);

/* Роуты для редиректов конец*/

$router->notFound(array(
    "controller" => "index",
    "action" => "route404"
));

$router->add(
    "/index/route404",
    array(
        "controller" => "index",
        "action" => "route404"
    )
);

$url = ($_GET['_url'] ?: $_SERVER["REQUEST_URI"]);

$router->handle($url);
