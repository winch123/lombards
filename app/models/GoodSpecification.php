<?php

namespace PolombardamModels;

class GoodSpecification extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $partner_id;

    /**
     *
     * @var integer
     */
    public $org_id;

    /**
     *
     * @var string
     */
    public $good_name;

    /**
     *
     * @var string
     */
    public $specification;

    public function initialize() {
        $this->setSource("good_specs");

        $this->hasMany("id", "Good", "specification_id");
    }

    /**
     *
     * @param int $partner_id
     * @param array $specification_data
     * @return array
     */
    public static function editFromApi($partner_id, $specification_data) {
        $good_name = $specification_data['name'];
        $org_id = $specification_data['org_id'];
        $text = $specification_data['specification'];

        $instance = self::findFirst([
                    "org_id = :org_id: AND good_name = :good_name:",
                    "bind" => [
                        'org_id' => (int) $org_id,
                        'good_name' => $good_name,
                    ],
        ]);

        if (!$instance) {
            $instance = new self();
            $instance->partner_id = (int) $partner_id;
            $instance->good_name = $good_name;
            $instance->org_id = (int) $org_id;
        }

        $instance->specification = $text;

        if ($instance->save()) {
            return [
                [
                    true,
                    'goods-specification-edit',
                    null,
                    'Successfully updated/created good specification',
                ]
            ];
        } else {
            return [
                [
                    false,
                    'goods-specification-edit',
                    null,
                    'An error has been occured when updating/creating good specification',
                ]
            ];
        }
    }

}
