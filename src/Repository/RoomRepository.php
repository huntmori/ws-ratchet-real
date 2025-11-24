<?php

namespace App\Repository;

use App\Model\Room;
use Medoo\Medoo;
use Psr\Log\LoggerInterface;

class RoomRepository extends BaseRepository
{
    public function __construct(Medoo $medoo, LoggerInterface $logger)
    {
        parent::__construct($medoo, $logger);
    }

    public function save(Room $room): ?Room
    {
        // idx가 초기화되지 않았거나 null이면 insert
        $reflection = new \ReflectionProperty($room, 'idx');
        if (!$reflection->isInitialized($room) || $room->idx === null) {
            return $this->insert($room);
        } else {
            return null;
        }
    }

    public function insert(Room $room): ?Room
    {
        $params = $room->toArray();

        $result = $this->medoo->insert('room', [
            'uuid' => $params['uuid'] ?? Medoo::raw('UUID()'),
            'room_name' => $params['room_name'] ?? null,
            'maximum_users' => $params['maximum_users'] ?? null,
            'join_type' => $params['join_type'] ?? null,
            'open_type' => $params['open_type'] ?? null,
            'join_password' => $params['join_password'] ?? Medoo::raw('null'),
            'created_datetime' => $params['created_datetime'] ?? Medoo::raw('now()'),
            'updated_datetime' => $params['updated_datetime'] ?? Medoo::raw('now()'),
            'is_deleted' => $params['is_deleted'] ?? Medoo::raw('false'),
            'deleted_datetime' => $params['deleted_datetime'] ?? Medoo::raw('null'),
            'room_state' => $params['room_state'] ?? null,
        ]);

        $this->logger->info('insert of \'room\' table is ', [$result]);

        $idx = $this->medoo->id();

        return $this->getOneByIdx((int)$idx);
    }

    public function getOneByIdx(int $idx): ?Room
    {
        $row = $this->medoo->get('room', '*', ['idx' => $idx]);

        if (!$row) {
            return null;
        }

        return Room::fromJson($row);
    }

    public function getOneByUuid($roomUuid) :?Room
    {
        $row = $this->medoo->get('room', '*', ['uuid' => $roomUuid]);

        return Room::fromJson($row);
    }

    public function getListByRoomUuid(array $array_map): array
    {
        if(empty($array_map)) {
            return [];
        }

        $row = $this->medoo->select('room', '*', ['uuid' => $array_map]);
        return array_map(fn($row) => Room::fromJson($row), $row);
    }
}