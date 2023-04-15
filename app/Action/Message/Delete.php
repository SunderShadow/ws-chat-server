<?php

namespace App\Action\Message;

use App\Model\Message\Message;
use App\Model\User\Sender;
use Core\Router\Request;
use Core\Router\Response;

class Delete
{
    public function __invoke(Sender $sender, Request $request): Response
    {
        $message = new Message();
        $message->id = $request->data['id'];
        $message->delete();

        return new Response([$sender->fd], 'message:delete', [
            'id' => $request->data['id'],
        ]);
    }
}