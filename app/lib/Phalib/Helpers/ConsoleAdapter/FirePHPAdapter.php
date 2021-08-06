<?php

namespace Phalib\Helpers\ConsoleAdapter;

use Phalib\Helpers\Console;

/**
 * Description of Console
 */
class FirePHPAdapter implements ConsoleAdapterInterface {

    /**
     * Console logger instance
     *
     * @var object
     */
    protected $instance;

    /**
     * Как будет отображаться каждый уровень отладки в консоли
     */
    protected static $log_lvl_association = array(
        Console::LOG => FirePHP::LOG,
        Console::INFO => FirePHP::INFO,
        Console::WARN => FirePHP::WARN,
        Console::ERROR => FirePHP::ERROR,
        Console::DUMP => FirePHP::DUMP,
        Console::TRACE => FirePHP::TRACE,
        Console::EXCEPTION => FirePHP::EXCEPTION,
        Console::AJAX => FirePHP::LOG,
    );

    /**
     * Возвращает аналогичный тип отладки в FirePHP/ChromePHP
     *
     * @param int $log_lvl
     * @return string
     */
    public static function getLogLevelAssociation($log_lvl) {
        return self::$log_lvl_association[(int) $log_lvl];
    }

    /**
     * Log object to firebug
     *
     * @see http://www.firephp.org/Wiki/Reference/Fb
     * @param mixed $Object
     * @return true
     * @throws Exception
     */
    public function send($object, $label, $log_lvl) {
        return call_user_func_array(array($this->instance, 'fb'), [$object, $label, $log_lvl]);
    }

    public function __construct() {
        $this->instance = FirePHP::getInstance(true);
    }

}
