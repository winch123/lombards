<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    array(
        $config->application->controllersDir,
        $config->application->modelsDir,
    )
)->register();

// For Namespaces
$loader->registerNamespaces(
    array(
       //...
       "PolombardamModels" => APP_PATH . "/app/models",
       "FirePHP"    => APP_PATH . "/app/lib/FirePHP",
       "Phalib"    => APP_PATH . "/app/lib/Phalib",
       "Polombardam" => APP_PATH . "/app/lib/Polombardam",
       'Phalcon' => APP_PATH . "/app/lib/Phalcon/"
    )
)->register();