<?php

namespace App\Repository;

use App\Model\User;
use Medoo\Medoo;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class UserRepository extends BaseRepository
{
    public function __construct(Medoo $medoo, LoggerInterface $logger)
    {
        parent::__construct($medoo, $logger);
    }

    public function save(User $user): ?User
    {
        return $user->idx === null ?
            $this->insert($user)
            :$this->update($user);
    }

    public function getOneByIdx(int $idx): ?User
    {
        $result = $this->medoo->get(
            'user',
            '*',
            ['idx' => $idx]
        );

        $this->logger->info('get of \'user\' table is ', $result);

        return User::fromJson($result);
    }

    private function insert(User $user): ?User
    {
        $this->logger->info('insert params :', $user->toArray());
        $result = $this->medoo->insert(
            'user',
            [
                'id' => $user->id,
                'password' => $user->password,
                'uuid' => $user->uuid ?: Medoo::raw('UUID()'),
                'created_at' => $user->createdAt ?: Medoo::raw('now()')
            ]
        );
        $idx = (int)$this->medoo->pdo->lastInsertId();
        return $this->getOneByIdx($idx);
    }

    private function update(User $user): ?User
    {
        return null;
    }

    public function existsById(string $id): bool
    {
        return $this->medoo->has(
            'user',
            [
                'id' => $id
            ]
        );
    }

    public function getOneById(string $id): ?User
    {
        $result = $this->medoo->get(
            'user',
            '*',
            ['id' => $id]
        );

        $this->logger->info('get of \'user\' table is ', $result);

        return User::fromJson($result);
    }

    public function getListByRoomUuid(string $roomUuid): array
    {
        $result = $this->medoo->select(
            'user',
            [
                    "[><]users_in_room" => ["uuid" => "user_uuid"],
                ],
            '*',
            ['users_in_room.room_uuid' => $roomUuid]
        );

        $this->logger->info('get of \'user\' table is ', $result);

        return array_map(function($row) {
            return User::fromJson($row);
        }, $result);
    }
}