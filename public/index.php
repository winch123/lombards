<?php

use Phalcon\Debug;
use Phalcon\Http\Response\StatusCode;
use Phalcon\Logger;
use Phalcon\Mvc\Application;
use Phalcon\Version;
use Phalib\Helpers\Console;
use Phalib\Helpers\ConsoleAdapter\ChromePHPAdapter;
use Phalib\Helpers\ConsoleAdapter\FirePHPAdapter;
use Polombardam\BotFilter;

if (Version::getPart(Version::VERSION_MAJOR) < 3 && Version::get() !== '2.0.14') {
    /*
     * Баг: https://github.com/phalcon/cphalcon/issues/12035
     * Подробнее в нашем баг-трекере: http://bugs.websmartshop.ru/thebuggenie/polom/issues/98
     */
    die('Используется несовместимая версия фреймворка!');
}

define('APP_PATH', realpath('..'));

/**
 * Read the configuration
 */
$config = include APP_PATH . "/app/config/config.php";

/**
 * Read auto-loader
 */
include APP_PATH . "/app/config/loader.php";

/**
 * Read services
 */
include APP_PATH . "/app/config/services.php";

if (BotFilter::isBot() && !BotFilter::isWhitelistedBot()) {
    // block not whitelisted crawlers(bots)
    header('HTTP/1.0 403 Forbidden');
    die;
}

/**
 * Handle the request
 */
$application = new Application($di);

$url = ($_GET['_url'] ?: $_SERVER["REQUEST_URI"]);

if (defined('APPLICATION_ENV') && APPLICATION_ENV == ENV_DEVELOPMENT) {
    // development
    if (defined('DEBUG_PRETTY_OUTPUT') && DEBUG_PRETTY_OUTPUT) {
        (new Debug())->listen();
    }

    Console::setLogLevelAll();

    if (defined('USE_FIREPHP_FALLBACK') && USE_FIREPHP_FALLBACK) {
        Console::useAdapter(new FirePHPAdapter());
    } else {
        Console::useAdapter(new ChromePHPAdapter());
    }

    if (defined('DEBUG_DATABASE') && DEBUG_DATABASE) {
        $adapter = new Logger\Adapter\Stream('../app/logs/db.log');
        // добавляем в вывод времени микросекунды
        $adapter->setFormatter(new Logger\Formatter\Line('[%date%][%type%] %message%', 'Y-m-d\TH:i:s.uP'));

        $logger = new Logger('db.log', ['main' => $adapter]);

        $events_manager = new Phalcon\Events\Manager();

        $events_manager->attach(
            'db:beforeQuery',
            function (Phalcon\Events\Event $event, $connection) use ($logger) {
                $sql = $connection->getSQLStatement();

                $logger->debug(sprintf(
                        '%s - [%s]',
                        $sql,
                        json_encode($connection->getSQLVariables())
                ));
            }
        );

        $connection = $di->getShared('db');

        // Assign the eventsManager to the db adapter instance
        $connection->setEventsManager($events_manager);
    }

    $response = $application->handle($url);

    if (defined('DEBUG_ROUTER') && DEBUG_ROUTER) {
        fb('<<<<<ROUTE');
        $router = $di->get('router');
        fb('URL is: ' . $url);

        // Check if some route was matched
        if ($router->wasMatched()) {
            $dispatcher = $di->getShared('dispatcher');

            fb('Route Name: ' . $router->getMatchedRoute()->getName());
            fb('Module: ' . $router->getModuleName());
            fb('Controller: ' . ucfirst($router->getControllerName()) . 'Controller');
            fb('Action: ' . ($router->getActionName() ?: 'index') . 'Action');
            fb('Params: ' . json_encode($dispatcher->getParams()));
        } else {
            fb('The route wasn\'t matched by any route');
        }
    }
} else {
    try {
        $response = $application->handle($url);
    } catch (Exception $e) {
        $error_text = vsprintf('Exception %s(%d): "%s"; TRACE: %s', [$e->getFile(), $e->getLine(), $e->getMessage(), $e->getTraceAsString()]);

        /** @var Logger $logger */
        $logger = $di->getShared('log');
        $logger->error($error_text);

        header("Location: /", true, StatusCode::FOUND);
        exit;
    }
}

$response->send();
