<?php

namespace PolombardamModels;

use Phalcon\Di;
use PolombardamModels\GoodCategory;
use PolombardamModels\GoodMetalStandart;

class GoodMetal extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $system;

    public function initialize() {
        $this->setSource("good_metal");

        $this->hasMany("id", "Good", "metal_id");
    }

    /**
     *
     * @param bool $only_system
     * @return GoodMetalStandart[]
     */
    public function getMetalStandarts($only_system = false) {
        $metal_standarts_criteria = GoodMetalStandart::query()
                ->where('metal_id = :metal_id:', ['metal_id' => $this->id]);

        if ($only_system) {
            $metal_standarts_criteria->andWhere('system = 1');
        }

        return $metal_standarts_criteria->execute();
    }

    /**
     *
     * @return GoodMetalStandart[]
     */
    public function getSystemMetalStandarts() {
        return $this->getMetalStandarts(true);
    }

    /**
     *
     * @return GoodCategory[]
     */
    public static function findSystemMetals() {
        return self::findAllMetals(true);
    }

    /**
     *
     * @param bool $only_system
     * @return GoodCategory[]
     */
    public static function findAllMetals($only_system = false) {
        $categories_criteria = self::query();

        if ($only_system) {
            $categories_criteria->andWhere('system = 1');
        }

        return $categories_criteria->execute();
    }

    /**
     *
     * @param int $organizationId
     * @param array $metal
     * @return array
     */
    public static function editFromApi($organizationId, $metal) {
        $connection = Di::getDefault()->get('db');

        $name_old = $metal['data']['name_old'];
        $name_new = $metal['data']['name_new'];

        if (!empty($name_old) && !empty($name_new)) {
            $metal_old = self::findFirst([
                        'name = :metal_name:',
                        'bind' => [
                            'metal_name' => $name_old,
                        ],
            ]);

            if ($metal_old) {
                $metal_new = self::findFirst([
                            'name = :metal_name:',
                            'bind' => [
                                'metal_name' => $name_new,
                            ],
                ]);

                if ($metal_new) {
                    $metal_id_new = $metal_new->id;
                } else {
                    $new_good_metal = new self();
                    $new_good_metal->name = $name_new;

                    if ($new_good_metal->save()) {
                        $metal_id_new = $new_good_metal->id;
                    }
                }

                if ($metal_id_new) {
                    $phql = "UPDATE `good` SET "
                            . "metal_id = '" . (int) $metal_id_new . "' "
                            . "WHERE metal_id = '" . (int) $metal_old->id . "' "
                            . "AND organization = '" . (int) $organizationId . "'";
                    $update_goods = $connection->query($phql);

                    if ($update_goods) {
                        return [
                            [
                                true,
                                'metal-edit',
                                $metal_id_new,
                                'Successfully updated goods'
                            ]
                        ];
                    } else {
                        $message = 'Error update goods';
                    }
                } else {
                    $message = 'Error adding new metal';
                }
            } else {
                $message = 'Old metal not found';
            }
        } else {
            $message = 'Empty data';
        }

        return [
            [
                false,
                'metal-edit',
                $metal_id_new,
                $message,
            ]
        ];
    }

}
