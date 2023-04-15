<?php

namespace App\Model\User;

use Core\ConnectedUsersRepository;
use Core\Router\Request;

class Sender extends User
{
    public readonly int $fd;

    public function __construct(
        Request $request,
        ConnectedUsersRepository $connectedUsers
    )
    {
        $this->id = $connectedUsers->get($this->fd = $request->fd);
    }
}