<?php

namespace PolombardamModels;

use Phalcon\Di;

class GoodMetalStandart extends ModelBase {

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
        $this->setSource("good_metal_standarts");

        $this->hasMany("id", "Good", "metal_standart_id");
    }

    /**
     *
     * @return GoodMetal|null
     */
    public function getMetal() {
        return GoodMetal::query()
                        ->innerJoin(GoodMetalStandartsRelations::class, 'gmsr.metal_id = ' . GoodMetal::class . '.id', 'gmsr')
                        ->where('gmsr.metal_standart_id = :metal_standart_id:', ['metal_standart_id' => $this->getId()])
                        ->limit(1)
                        ->execute()
                        ->getFirst();
    }

    /**
     *
     * @param int $organizationId
     * @param array $metal_standart
     * @return array
     */
    public static function editFromApi($organizationId, $metal_standart) {
        $connection = Di::getDefault()->get('db');

        $name_old = $metal_standart['data']['name_old'];
        $name_new = $metal_standart['data']['name_new'];

        if (!empty($name_old) && !empty($name_new)) {
            $metal_standart_old = GoodMetalStandart::findFirst([
                        'name = :metal_standart_name:',
                        'bind' => [
                            'metal_standart_name' => $name_old,
                        ],
            ]);

            if ($metal_standart_old) {
                $metal_standart_new = GoodMetalStandart::findFirst([
                            'name = :metal_standart_name:',
                            'bind' => [
                                'metal_standart_name' => $name_new,
                            ],
                ]);

                if ($metal_standart_new) {
                    $metal_standart_id_new = $metal_standart_new->id;
                } else {
                    $new_good_metal = new GoodMetalStandart();
                    $new_good_metal->name = $name_new;

                    if ($new_good_metal->save()) {
                        $metal_standart_id_new = $new_good_metal->id;
                    }
                }

                if ($metal_standart_id_new) {
                    $phql = "UPDATE `good` SET "
                            . "metal_standart_id = '" . (int) $metal_standart_id_new . "' "
                            . "WHERE metal_standart_id = '" . (int) $metal_standart_old->id . "' "
                            . "AND organization = '" . (int) $organizationId . "'";
                    $update_goods = $connection->query($phql);

                    if ($update_goods) {
                        return [
                            [
                                true,
                                'metal-standart-edit',
                                $metal_standart_id_new,
                                'Successfully updated goods'
                            ]
                        ];
                    } else {
                        $message = 'Error update goods';
                    }
                } else {
                    $message = 'Error adding new metal standart';
                }
            } else {
                $message = 'Old metal standart not found';
            }
        } else {
            $message = 'Empty data';
        }

        return [
            [
                false,
                'metal-standart-edit',
                $metal_standart_id_new,
                $message,
            ]
        ];
    }

}
