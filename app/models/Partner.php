<?php

namespace PolombardamModels;

class Partner extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $hash;

    public function initialize() {
        $this->setSource('partner');
    }

}
