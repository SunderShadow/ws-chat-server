<?php

use Core\DI;
use Core\Router\Router;
use Core\Router\RouteResolver;
use Core\Swoole\Adapter\FrameToRequestAdapter;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

require __DIR__ . "/../vendor/autoload.php";

// Dependency injection init
$di = new DI(require "dependencies.php");

// Router init
$routes = require "routes.php";
$routeResolver = new RouteResolver($di);

$router = new Router($routes, $routeResolver);
$router->onUndefined(function () {
    echo "undefined";
});

$server = new Server('0.0.0.0', 8081);

$server->on('message', function (Server $server, Frame $frame) use ($router) {
    try {
        $request = new FrameToRequestAdapter($frame);
        $response = $router->resolve($request);
        foreach ($response->to as $fd) {
            $server->push($fd, json_encode([
                'action' => $response->actionName,
                'data'   => $response->data
            ]));
        }
    } catch (\Core\Swoole\Adapter\InvalidRequest $e) {
        $server->push($frame->fd, json_encode([
            'action' => 'error:request',
            'message' => $e->getMessage()
        ]));
    }
});

$server->start();