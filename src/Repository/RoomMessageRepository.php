<?php

namespace App\Repository;

use App\Enum\ChatType;
use App\Model\RoomMessage;
use Medoo\Medoo;
use Psr\Log\LoggerInterface;

class RoomMessageRepository extends BaseRepository
{
    public function __construct(Medoo $medoo, LoggerInterface $logger)
    {
        parent::__construct($medoo, $logger);
    }

    public function save(RoomMessage $message): ?RoomMessage
    {
        if($message->idx() === null) {
            return $this->insert($message);
        } else {
            return $this->update($message);
        }
    }

    private function insert(RoomMessage $message): ?RoomMessage
    {
        $result = $this->medoo->insert(
            'room_message',
            [
                'uuid' => $message->uuid() ?: Medoo::raw('UUID()'),
                'room_uuid' => $message->roomUuid(),
                'user_uuid' => $message->userUuid(),
                'type' => $message->type()->value ?: ChatType::SIMPLE_TEXT->value,
                'message' => $message->message(),
                'created_datetime' => $message->createdDatetime() ?: Medoo::raw('NOW()')
            ]
        );

        if(!$result) {
            $this->logger->error('Failed to insert room message', ['message' => $message->toArray()]);
            return null;
        }

        $idx = $this->medoo->id();
        $row = $this->medoo->get('room_message', '*', ['idx' => $idx]);
        return RoomMessage::fromJson($row);
    }

    private function update(RoomMessage $message): ?RoomMessage
    {
        return null;
    }
}