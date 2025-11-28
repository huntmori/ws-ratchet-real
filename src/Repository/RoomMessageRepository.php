<?php

namespace App\Repository;

use App\Enum\ChatType;
use App\Model\RoomMessage;
use Medoo\Medoo;
use Psr\Log\LoggerInterface;

/**
 * RoomMessageRepository 클래스
 *
 * 채팅 메시지 데이터에 대한 데이터베이스 작업을 담당하는 Repository
 * RoomMessage 모델의 CRUD 작업 및 메시지 조회 기능을 제공합니다.
 */
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