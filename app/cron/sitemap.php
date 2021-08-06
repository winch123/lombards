<?php

use Phalcon\Mvc\Micro;
use Polombardam\Sitemap;

define('APP_PATH', (__DIR__) . '/../..');

$config = include APP_PATH . "/app/config/config.php";
include APP_PATH . "/app/config/loader.php";
include APP_PATH . "/app/config/services.php";

$app = new Micro();

$goods_parts = Sitemap::createGoodsMap();

$cities_parts = Sitemap::createCitiesMap();

$merchants_parts = Sitemap::createMerchantsMap();

$pages_parts = Sitemap::createPagesMap();

$tags_parts = Sitemap::createTagsMap();

$categories_parts = Sitemap::createCategoriesMap();

$sitemap_config = [
    'goods' => [
        'changefreq' => 'daily',
        'parts' => $goods_parts,
    ],
    'cities' => [
        'changefreq' => 'weekly',
        'parts' => $cities_parts,
    ],
    'merchants' => [
        'changefreq' => 'weekly',
        'parts' => $merchants_parts,
    ],
    'pages' => [
        'changefreq' => 'weekly',
        'parts' => $pages_parts,
    ],
    'tags' => [
        'changefreq' => 'weekly',
        'parts' => $tags_parts,
    ],
    'categories' => [
        'changefreq' => 'weekly',
        'parts' => $categories_parts,
    ],
];

Sitemap::createIndexXML($sitemap_config);
