<?php

namespace PolombardamModels;

class GoodImage extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $good_id;

    /**
     *
     * @var string
     */
    public $src;

    /**
     *
     * @var string
     */
    public $preview;

    /**
     *
     * @var integer
     */
    public $main;

    public function initialize() {
        $this->belongsTo("good_id", "Good", "id");

        $this->setSource('good_image');
    }

}
