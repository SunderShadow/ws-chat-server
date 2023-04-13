<?php

use Core\DI;
use Core\Router\Router;
use Core\Router\RouteResolver;
use Core\Swoole\Adapter\FrameToRequestAdapter;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

require __DIR__ . "/../vendor/autoload.php";

// Env init
$env = new \Core\Env();
$env->load(__DIR__ . '/../.env');

// DB Connection init
$dsn = $env['DB_DRIVER'] . ':host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'];
$connection = new \Core\Connection\DB($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);

// Boot models
$bootModels = require __DIR__ . "/../app/Model/kernel.php";
$bootModels($connection);

// Connected users init
$connectedUsers = new \Core\ConnectedUsersRepository();

// Dependency injection init
$di = new DI([
    $env::class => $env,
    $connection::class => $connection,
    $connectedUsers::class => $connectedUsers,
    \App\Model\User\Sender::class => function (DI $di) {
        return new \App\Model\User\Sender($di->get(\Core\Router\Request::class), $di->get(\Core\ConnectedUsersRepository::class));
    }
]);

// Router init
$router = new Router(require "routes.php", new RouteResolver($di));
$router->onUndefined(function () {
    echo "undefined";
});

$server = new Server($env['SERVER_HOST'], $env['SERVER_PORT']);

$server->on('open', function (Server $server, \Swoole\Http\Request $request) use ($connectedUsers){
    $connectedUsers->add($request->fd, 1);
});

$server->on('message', function (Server $server, Frame $frame) use ($router) {
    try {
        $response = $router->resolve(new FrameToRequestAdapter($frame));

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

$server->on('close', function (Server $server, int $fd) use ($connectedUsers){
    $connectedUsers->remove($fd);
});

$server->start();