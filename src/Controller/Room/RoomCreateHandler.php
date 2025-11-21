<?php

namespace App\Controller\Room;

use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Repository\RoomRepository;
use App\Request\Room\RoomCreatePayload;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

readonly class RoomCreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RoomRepository $roomRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        $this->logger->debug(__METHOD__. " is called");;
        // TODO: Implement handle() method.
        // dto 변환
        // room create
        // room insert
        // room select
        // room join
        // send dto
    }

    public function getEventName(): string
    {
        return RoomCreatePayload::EVENT_NAME;
    }
}