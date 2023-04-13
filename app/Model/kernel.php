<?php

use Core\Connection\DB;

return function (DB $db) {
    \App\Model\User\User::boot($db);
    \App\Model\User\Sender::boot($db);
    \App\Model\Message\Message::boot($db);
};