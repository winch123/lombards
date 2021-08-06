<?php

use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;

// Using the CLI factory default services container
$di = new CliDI();

define('APP_PATH', realpath('..'));

/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new Loader();

$loader->registerDirs([
    __DIR__ . "/tasks",
    __DIR__ . "/models",
]);

$loader->registerNamespaces(
        array(
            //...
            "PolombardamModels" => APP_PATH . "/app/models",
            "FirePHP" => APP_PATH . "/app/lib/FirePHP",
            "Phalib" => APP_PATH . "/app/lib/Phalib",
            "Polombardam" => APP_PATH . "/app/lib/Polombardam",
            'Phalcon' => APP_PATH . "/app/lib/Phalcon/"
        )
);
$loader->register();

// Load the configuration file (if any)
$configFile = __DIR__ . "/config/config.php";

if (is_readable($configFile)) {
    $config = include $configFile;

    $di->set("config", $config);
}

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function () use ($config) {
    $dbConfig = $config->database->toArray();
    $adapter = $dbConfig['adapter'];
    unset($dbConfig['adapter']);

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

    return new $class($dbConfig);
});

// Create a console application
$console = new ConsoleApp();

$console->setDI($di);

/**
 * Process the console arguments
 */
$arguments = [];

foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments["task"] = $arg;
    } elseif ($k === 2) {
        $arguments["action"] = $arg;
    } elseif ($k >= 3) {
        $arguments["params"][] = $arg;
    }
}

try {
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();

    exit(255);
}