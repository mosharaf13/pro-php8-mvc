<?php

require_once __DIR__ . '/../vendor/autoload.php';

$router = new \Framework\Routing\Router();

$routes = require_once '../src/routes.php';

$routes($router);

print $router->dispatch();