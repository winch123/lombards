<?php

namespace Phalib\Helpers\ConsoleAdapter;

use Phalib\Helpers\Console;

/**
 * Description of Console
 */
class ChromePHPAdapter implements ConsoleAdapterInterface {

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
        Console::LOG => ChromePhp::LOG,
        Console::INFO => ChromePhp::INFO,
        Console::WARN => ChromePhp::WARN,
        Console::ERROR => ChromePhp::ERROR,
        Console::DUMP => ChromePhp::INFO,
        Console::TRACE => ChromePhp::INFO,
        Console::EXCEPTION => ChromePhp::ERROR,
        Console::AJAX => ChromePhp::LOG,
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
     * Sending data to log adapted
     *
     * @param mixed $object
     * @param string $label
     * @param int $log_lvl
     * @return boolean
     */
    public function send($object, $label, $log_lvl) {
        $this->instance->addSetting(ChromePhp::BACKTRACE_LEVEL, 5);

        $params = (isset($label) && is_string($label) ? [$label . ':', $object] : [$object]);

        $result = call_user_func_array(array($this->instance, $log_lvl), $params);
        // reverting changes
        $this->instance->addSetting(ChromePhp::BACKTRACE_LEVEL, 1);

        return $result;
    }

    public function __construct() {
        $this->instance = ChromePhp::getInstance();
    }

}
