<?php

namespace App\Controller;

use Ratchet\ConnectionInterface;

interface RequestHandlerInterface
{
    public function handle(ConnectionInterface $from, $data): void;
    public function getEventName(): string;
}