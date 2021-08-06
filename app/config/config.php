<?php

defined('APP_PATH') || define('APP_PATH', realpath('.'));

defined('ENV_TESTING') || define('ENV_TESTING', 'testing');
defined('ENV_DEVELOPMENT') || define('ENV_DEVELOPMENT', 'development');
defined('ENV_PRODUCTION') || define('ENV_PRODUCTION', 'production');

if (file_exists(APP_PATH . '/app/config/production.php')) {
    return include APP_PATH . '/app/config/production.php';
} else if (file_exists(APP_PATH . '/app/config/develop.php')) {
    return include APP_PATH . '/app/config/develop.php';
}
