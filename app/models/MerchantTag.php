<?php

namespace PolombardamModels;

class MerchantTag extends ModelBase {

    /**
     *
     * @var integer
     */
    public $merchant_id;

    /**
     *
     * @var integer
     */
    public $tag_id;

    /**
     *
     * @return self[]
     */
    public static function getAllMerchantsTags() {
        $instance = new self();
        $connection = $instance->getReadConnection();

        $sql = "SELECT
            merchant.city as merchant_city,
            merchant_tag.tag_id as tag_id FROM merchant
            INNER JOIN merchant_tag ON merchant.id = merchant_tag.merchant_id
            INNER JOIN tag ON tag.id = merchant_tag.tag_id
            WHERE merchant.deleted IS NULL
            GROUP BY merchant.city, merchant_tag.tag_id";

        $rows = $connection->query($sql)->fetchAll();

        return $rows;
    }

    public function initialize() {
        $this->setSource('merchant_tag');
    }

}
