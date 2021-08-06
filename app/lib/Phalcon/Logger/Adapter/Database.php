<?php

namespace Phalcon\Logger\Adapter;

use Phalcon\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Phalcon\Db\Column;
use Phalcon\Logger\Adapter\AbstractAdapter as LoggerAdapter;
use Phalcon\Logger\Adapter\AdapterInterface;
use Phalcon\Logger\Exception;
use Phalcon\Logger\Item;

/**
 * Database Logger
 *
 * Adapter to store logs in a database table
 *
 * @package Phalcon\Logger\Adapter
 * @see https://github.com/phalcon/incubator-logger/blob/master/src/Adapter/Database.php
 */
class Database extends LoggerAdapter implements AdapterInterface {

    /**
     * Name
     * @var string
     */
    protected $name = 'phalcon';

    /**
     * Adapter options
     * @var array
     */
    protected $options = [];

    /**
     * @var DbAdapterInterface
     */
    protected $db;

    /**
     * Class constructor.
     *
     * @param  string $name
     * @param  array  $options
     * @throws Exception
     */
    public function __construct($name = 'phalcon', array $options = []) {
        if (!isset($options['db'])) {
            throw new Exception("Parameter 'db' is required");
        }

        if (!$options['db'] instanceof DbAdapterInterface) {
            throw new Exception("Parameter 'db' must be object and implement AdapterInterface");
        }

        if (!isset($options['table'])) {
            throw new Exception("Parameter 'table' is required");
        }

        $this->db = $options['db'];

        if ($name) {
            $this->name = $name;
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close(): bool {
        if ($this->db->isUnderTransaction()) {
            $this->db->commit();
        }

        $this->db->close();

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function begin(): AdapterInterface {
        $this->db->begin();

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function commit(): AdapterInterface {
        $this->db->commit();

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function rollback(): AdapterInterface {
        $this->db->rollback();

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param Item $item
     * @return void
     */
    public function process(Item $item): void {
        $this->db->execute(
                'INSERT INTO ' . $this->options['table'] . ' VALUES (null, ?, ?, ?, ?, ?)',
                [
                    $this->name,
                    $item->getType(),
                    $this->getFormatter()->format($item),
                    $item->getTime(),
                    json_encode($item->getContext()),
                ],
                [
                    Column::BIND_PARAM_STR,
                    Column::BIND_PARAM_INT,
                    Column::BIND_PARAM_STR,
                    Column::BIND_PARAM_INT,
                    Column::BIND_PARAM_STR,
                ]
        );
    }

}
