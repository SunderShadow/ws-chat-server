<?php

namespace App\Action\Message;

use App\Model\Message\Message;
use App\Model\User\Sender;
use Core\Router\Request;
use Core\Router\Response;

class Update
{
    public function __invoke(Sender $sender, Request $request): Response
    {
        $message = new Message();
        $message->id = $request->data['id'];
        $message->read();
        var_dump($message);
        $message->text = $request->data['text'];
        $message->update();

        return new Response([$sender->fd], 'message:update', [
            'id'        => $message->id,
            'text'      => $message->text
        ]);
    }
}