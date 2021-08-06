<?php

use Phalcon\Cli\Task;
use PolombardamModels\GoodCategory;

class Migration111Task extends Task {

    public function mainAction() {
        echo "Migration: you should choose action(migrate|rollback) to continue" . PHP_EOL;
    }

    public function migrateAction() {
        $parent_category = GoodCategory::findFirst(["name = 'Мобильные телефоны'"]);

        $subcategories_list = [
            'Acer',
            'Alcatel',
            'ASUS',
            'Blackberry',
            'BQ',
            'DEXP',
            'Explay',
            'Fly',
            'Highscreen',
            'HTC',
            'Huawei',
            'iPhone',
            'Lenovo',
            'LG',
            'Meizu',
            'Micromax',
            'Microsoft',
            'Motorola',
            'MTS',
            'Nokia',
            'Panasonic',
            'Philips',
            'Prestigio',
            'Samsung',
            'Siemens',
            'Sky Link',
            'Sony',
            'teXet',
            'Vertu',
            'Xiaomi',
            'ZTE',
            'Другие марки',
            'Модемы и роутеры',
            'Рации',
        ];

        foreach ($subcategories_list as $subcategory_name) {
            $category = GoodCategory::findFirst(["name = '" . $subcategory_name . "'"]);

            if (!$category) {
                $category = new GoodCategory();
                $category->name = $subcategory_name;
            }

            echo 'Processing category: ' . $category->name . PHP_EOL;

            $category->parent_id = $parent_category->id;
            $category->system = 1;
            $category->save();
        }

        echo "Migration: finished" . PHP_EOL;
    }

    public function rollbackAction() {
        echo "Migration: rollback not required any actions";
    }

}
