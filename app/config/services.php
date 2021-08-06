<?php
/**
 * Services are globally registered in this file
 *
 * @var Config $config
 */

use Phalcon\Cache;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault;
use Phalcon\Escaper;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Http\Response\Cookies;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Database;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\ViewBaseInterface;
use Phalcon\Session\Adapter\Stream as SessionStream;
use Phalcon\Session\Manager;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Url as UrlResolver;
use Phalib\Breadcrumbs\Breadcrumbs;
use Phalib\Helpers\Console;
use Polombardam\MetaTag;

/**
 * Sends the given data to the FirePHP/ChromePHP Firefox Extension.
 * The data can be displayed in the Firebug Console or in the
 * "Server" request tab.
 *
 * @param mixed $data
 * @param string|null $label
 * @return bool
 */
function fb($data, $label = null) {
    return Console::log(Console::LOG, $data, $label);
}

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () use ($config, $di) {
    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines(array(
        '.volt' => function (ViewBaseInterface $view) use ($config, $di) {
            $volt = new VoltEngine($view, $di);

            $volt->setOptions(array(
                'path' => $config->application->cacheDir,
                'separator' => '_',
                'always' => APPLICATION_ENV === ENV_DEVELOPMENT,
            ));

            //$volt->getCompiler->addFunction('strtotime', 'strtotime');
            $volt->getCompiler()->addFunction(
                    'superdate',
                    function($date) {
                        return "date('d.m.Y H:i', strtotime({$date}))";
                    }
            );

            return $volt;
        },
        '.phtml' => Php::class,
    ));

    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use ($config) {
    $dbConfig = $config->database->toArray();
    $adapter = $dbConfig['adapter'];
    unset($dbConfig['adapter']);

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

    return new $class($dbConfig);
});

/**
 * Logger to Database
 */
$di->setShared('log', function () use ($di) {
    $adapter = new Database(null, ['db' => $di->getShared('db'), 'table' => 'logs']);

    $logger  = new Logger(
        'messages',
        [
            'main' => $adapter,
        ]
    );

    return $logger;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->setShared('flash', function () {
    $escaper = new Escaper();

    $flash = new Flash($escaper);
    $flash->setImplicitFlush(true);

    $flash->setCssClasses([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ]);

    return $flash;
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () use ($config) {
    if (PHP_VERSION_ID < 70300) {
        session_set_cookie_params(0, '/; samesite=Lax;', $_SERVER['HTTP_HOST'], false, false);
    } else {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
    }

    $session = new Manager();

    $files = new SessionStream(
        [
            'savePath' => ($config->session->path ?: session_save_path()),
        ]
    );

    $session->setAdapter($files);
    $session->start();

    return $session;
});

$di->set(
    'cookies',
    function () {
        $cookies = new Cookies();
        $cookies->useEncryption(false);

        return $cookies;
    }
);

/**
 * Add routing capabilities
 */
$di->setShared(
    'router',
    function () {
        require 'routes.php';

        return $router;
    }
);

$di->setShared('crumbs', function() {
    return new Breadcrumbs();
});

$di->setShared('meta', function() {
    return new MetaTag();
});

$di->setShared('config', function () use ($config) {
    return $config;
});

$di->setShared('fcache', function () use ($config) {
    $serializer_factory = new SerializerFactory();

    // Set the cache directory
    $fcache_options = [
        // Cache the file for 60 minutes
        "lifetime" => 60 * 60,
        "storageDir" => $config->application->cacheDir,
    ];

    $adapter = new Stream($serializer_factory, $fcache_options);

    return new Cache($adapter);
});
