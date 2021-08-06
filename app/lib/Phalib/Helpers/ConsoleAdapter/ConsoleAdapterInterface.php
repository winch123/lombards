<?php

namespace Phalib\Helpers\ConsoleAdapter;

/**
 * Description of Console
 */
interface ConsoleAdapterInterface {

    /**
     * Возвращает аналогичный тип отладки в FirePHP/ChromePHP
     *
     * @param int $log_lvl
     * @return string
     */
    public static function getLogLevelAssociation($log_lvl);

    /**
     * Sending data to log adapted
     *
     * @param mixed $object
     * @param string $label
     * @param int $log_lvl
     * @return boolean
     */
    public function send($object, $label, $log_lvl);

}
