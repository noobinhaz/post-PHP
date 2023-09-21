<?php

require_once __DIR__ . '/DB.php'; //require DB class
require_once __DIR__ . '/Router.php'; //require Router class
require_once __DIR__ . '/../routes.php'; //require routes.php to indicate which file to
// server in case of which URI
require_once __DIR__ . '/../config.php'; //require basic config for this application
$router = new Router;
$router->setRoutes($routes);
$url = $_SERVER['REQUEST_URI'];
require_once __DIR__ . "/../api/" . $router->getFilename($url);
