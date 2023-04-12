<?php

namespace App\Model\User;

use Core\Router\Request;

class Sender extends User
{
    public function __construct(
        Request $request
    )
    {
        parent::__construct($request->fd);
    }
}