<?php

namespace App\Repository;

use App\Enum\InRoomStatus;
use App\Exception\ApiException;
use App\Model\User;
use App\Model\UsersInRoom;
use Medoo\Medoo;
use Psr\Log\LoggerInterface;

class UsersInRoomRepository extends BaseRepository
{
    public function __construct(Medoo $medoo, LoggerInterface $logger)
    {
        parent::__construct($medoo, $logger);
    }

    /**
     * @throws ApiException
     */
    public function save(UsersInRoom $inRoom): ?UsersInRoom
    {
        if($inRoom->idx === null) {
            return $this->insert($inRoom);
        } else {
            return $this->update($inRoom);
        }
    }

    /**
     * @throws ApiException
     */
    public function insert(UsersInRoom $inRoom): ?UsersInRoom
    {
        $result = $this->medoo->insert(
            'users_in_room',
            [
                'user_uuid' => $inRoom->userUuid,
                'room_uuid' => $inRoom->roomUuid,
                'state' => $inRoom->state->value ?: InRoomStatus::JOIN->value,
                'created_datetime' => $inRoom->createdDatetime ?: Medoo::raw('NOW()'),
                'updated_datetime' => $inRoom->updatedDatetime ?: Medoo::raw('NOW()')
            ]
        );

        $idx = $this->medoo->id();

        if($result && $idx === false) {
            throw new ApiException(
                message: 'error wile room joining',code: -1
            );
        }

        return $this->getOneByIdx((int)$idx);
    }

    public function update(UsersInRoom $inRoom): ? UsersInRoom
    {
        return null;
    }

    public function getOneByIdx(?string $idx): ?UsersInRoom
    {
        $row = $this->medoo->get(
            'users_in_room',
            '*',
            [
                'idx' => $idx
            ]
        );

        return UsersInRoom::fromJson($row);
    }

    /**
     * @param string|null $uuid
     * @return array<UsersInRoom>
     */
    public function getListByUserUuid(?string $uuid): array
    {
        $row = $this->medoo->select(
            'users_in_room',
            '*',
            ['user_uuid' => $uuid]
        );

        return array_map(fn($row) => UsersInRoom::fromJson($row), $row);
    }

    public function existsByRoomUUidAndUserUuid($roomUuid, string $userUuid): bool
    {
        return $this->medoo->has(
            'users_in_room',
            [
                'room_uuid' => $roomUuid,
                'user_uuid' => $userUuid
            ]);
    }

    /**
     * @param string $roomUuid
     * @return array<UsersInRoom>
     */
    public function getListByRoomUuid(string $roomUuid): array
    {
        $rows = $this->medoo->select(
            'users_in_room',
            '*',
            [
                'room_uuid' => $roomUuid,
            ]);
        return array_map(fn($row) => UsersInRoom::fromJson($row), $rows);
    }
}