<?php

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);
$c = $app->getContainer();

$c['errorHandler'] = function ($c) {
    return new App\Handlers\apiError();
};


// Set up dependencies
$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($app);

// Register routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// Register my app
require($_SERVER['DOCUMENT_ROOT']. '/biblioFRD-Server/app/app_loader.php');

// Register middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Run app
$app->run();
