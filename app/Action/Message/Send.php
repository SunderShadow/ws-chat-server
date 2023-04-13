<?php

namespace App\Action\Message;

use App\Model\Message\Message;
use App\Model\User\Sender;
use Core\Router\Request;
use Core\Router\Response;

class Send
{
    public function __invoke(Sender $sender, Request $request): Response
    {
        $message = new Message();
        $message->text = $request->data['text'];
        $sender->attachMessage($message);

        return new Response([$sender->fd], 'message:send', [
            'id'        => $message->id,
            'text'      => $message->text,
            'sender_id' => $message->sender_id
        ]);
    }
}