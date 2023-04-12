<?php
require __DIR__ . "/../vendor/autoload.php";

// Dependency injection init
$di = new \Core\DI(require "dependencies.php");

// Router init
$routes = require "routes.php";
$routeResolver = new \Core\Router\RouteResolver($di);

$router = new \Core\Router\Router($routes, $routeResolver);
$router->onUndefined(function () {
    echo "undefined";
});


if ($debug = false) {
    $router->resolve('action');
} else {
    try {
        $router->resolve('action');
    } catch (ReflectionException) {
        echo "internal server error";
    }
}