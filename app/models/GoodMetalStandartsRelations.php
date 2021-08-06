<?php

namespace PolombardamModels;

class GoodMetalStandartsRelations extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $metal_id;

    /**
     *
     * @var integer
     */
    public $metal_standart_id;

    public function initialize() {
        $this->setSource("good_metal_standarts_relations");
    }

}
