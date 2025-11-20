<?php

namespace App\Controller;

use Ratchet\ConnectionInterface;

class UserCreateHandler implements RequestHandlerInterface
{
    public const string EVENT_NAME = "user.create";
    public function handle(ConnectionInterface $from, $data): void
    {
        // TODO: Implement handle() method.
        // RequestDto 변환
        // 유효성 검사
        // Model생성
        // Repo->insert
        // 결과-> dto
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }
}