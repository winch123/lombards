<?php

namespace PolombardamModels;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\ResultsetInterface;

class ModelBase extends Model {

    /**
     *
     * @return int
     */
    public function getId() {
        return (int) $this->id;
    }

    /**
     *
     * @return array
     */
    public function getMessagesNormalized() {
        return array_map(function ($item) {
            return (string) $item;
        }, $this->getMessages());
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return static[]|static
     */
    public static function find($parameters = null): ResultsetInterface {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return static|false
     */
    public static function findFirst($parameters = null) {
        return parent::findFirst($parameters);
    }

}
