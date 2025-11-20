<?php

namespace App\Controller\User;

use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Request\User\UserLoginPayload;
use Ratchet\ConnectionInterface;

class UserLoginHandler implements RequestHandlerInterface
{
    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        // TODO: Implement handle() method.
    }

    public function getEventName(): string
    {
        return UserLoginPayload::EVENT_NAME;
    }
}