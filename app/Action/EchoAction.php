<?php

namespace App\Action;

use Core\Router\Request;
use Core\Router\Response;

class EchoAction
{
    public function __invoke(Request $request): Response
    {
        return new Response([$request->fd], $request->action, $request->data);
    }
}