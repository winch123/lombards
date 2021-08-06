<?php

namespace PolombardamModels;

class MerchantImage extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $merchant_id;

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

    public function initialize() {
        $this->belongsTo("merchant_id", Merchant::class, "id");

        $this->setSource('merchant_image');
    }

}
