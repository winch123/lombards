<?php

namespace Phalib\Helpers;

use Phalib\Helpers\ConsoleAdapter\ConsoleAdapterInterface;

/**
 * Description of Console
 */
class Console {

    /**
     * Log level
     *
     * @var int
     */
    const DISABLED = 0; // особый
    const LOG = 1;
    const INFO = 2;
    const WARN = 3;
    const ERROR = 4;
    const AJAX = 5; // особый
    const DUMP = 6;
    const TRACE = 7;
    const EXCEPTION = 8;

    /**
     * Название уровня отладки(описание)
     */
    protected static $log_lvl_names = array(
        self::LOG => 'LOG',
        self::INFO => 'INFO',
        self::WARN => 'WARN',
        self::ERROR => 'ERROR',
        self::DUMP => 'DUMP',
        self::TRACE => 'TRACE',
        self::EXCEPTION => 'EXCEPTION',
        self::AJAX => 'AJAX (вкл./откл. вывод в консоль у AJAX)',
    );

    /**
     * Текущий уровень отладки, может быть как числом так и массивом
     * используется для проверки какие типы(уровни) нужно отображать в консоли
     */
    protected static $log_lvl;

    /**
     * Хранится адапер для FirePHP/ChromePHP
     *
     * @var ConsoleAdapterInterface
     */
    protected static $log_adapter;

    public static function useAdapter(ConsoleAdapterInterface $adapter) {
        self::$log_adapter = $adapter;
    }

    /**
     * Здесь в массиве все перечисляются доступные уровни отладки.
     *
     * @return array
     */
    public static function getAllLogLevels() {
        return array_keys(self::$log_lvl_names);
    }

    /**
     * Возвращает название уровня отладки
     *
     * @param int $log_lvl
     * @return string
     */
    public static function getLogLevelName($log_lvl) {
        return self::$log_lvl_names[(int) $log_lvl];
    }

    /**
     * Возвращает аналогичный тип отладки в FirePHP/ChromePHP
     *
     * @param int $log_lvl
     * @return string
     */
    public static function getLogLevelAssociation($log_lvl) {
        return self::$log_adapter->getLogLevelAssociation($log_lvl);
    }

    /**
     * Log object to log adapter
     *
     * @param mixed $Object
     * @return bool
     */
    public static function send($object, $label, $log_lvl) {
        return self::$log_adapter->send($object, $label, $log_lvl);
    }

    /**
     * Функция отправки отладочной информации в консоль браузера.
     *
     * @param int|array $log_lvls
     * @param mixed $Object
     * @param string $Label
     * @return boolean
     */
    public static function log($log_lvls, $Object, $Label = null) {
        if (self::isAjaxRequest()) {
            if (!self::isNeedToShow(self::AJAX)) {
                return false;
            }
        }

        if (is_int($log_lvls)) {
            $log_lvls = array($log_lvls);
        }

        foreach ($log_lvls as $log_lvl) {
            if (self::isNeedToShow($log_lvl)) {
                return self::send($Object, $Label, self::getLogLevelAssociation($log_lvl));
            }
        }

        return false;
    }

    /**
     * Устанавливаем уровень отладки для отображения.
     * Используется НЕ битовая маска.
     *
     * @param int|array $log_lvl
     */
    public static function setLogLevel($log_lvl) {
        self::$log_lvl = $log_lvl;

        return true;
    }

    /**
     * Функция включает все уровни отладки.
     *
     * @return boolean
     */
    public static function setLogLevelAll() {
        self::$log_lvl = self::getAllLogLevels();

        return true;
    }

    /**
     * Функция возвращает текущий установленный уровень отладки.
     * Может быть как массивом, так и простым
     * числом(вместо массива из одного элемента).
     *
     * @return array|int
     */
    public static function getCurrentLogLevel() {
        return self::$log_lvl;
    }

    /**
     * Проверяем нужно ли показывать данную отладку.
     * Вообще функцию можно использовать даже просто для проверки вхождения
     * уровня отладки в текущий установленный уровень отладки.
     *
     * @param int|array $log_lvl
     * @return boolean
     */
    public static function isNeedToShow($log_lvl) {
        if (is_int(self::$log_lvl)) {
            return $log_lvl === self::$log_lvl ? true : false;
        } elseif (is_array(self::$log_lvl)) {
            if (in_array($log_lvl, self::$log_lvl)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Проверка текущего запроса: AJAX или нет.
     *
     * @return boolean
     */
    public static function isAjaxRequest() {
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверка включена ли отладка.
     *
     * @return boolean
     */
    public static function isEnabled() {
        return (self::isNeedToShow(self::DISABLED) ? false : true);
    }

    /**
     * Проверяет включена ли отладка у пользователя.
     * Для проверки используется уровень отладки(битовая маска).
     *
     * @param int $log_lvl_mask
     * @return boolean
     */
    public static function isEnabledForUserFromBitMask($log_lvl_mask) {
        return ($log_lvl_mask === convertToBitMask(self::DISABLED) ? false : true);
    }

}
