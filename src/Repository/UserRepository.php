<?php

namespace App\Repository;

use App\Model\User;
use Medoo\Medoo;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * UserRepository 클래스
 *
 * 사용자 데이터에 대한 데이터베이스 작업을 담당하는 Repository
 * User 모델의 CRUD 작업 및 사용자 조회 기능을 제공합니다.
 */
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

    public function existsByUuid(string $userUuid): bool
    {
        return $this->medoo->has('user', ['uuid' => $userUuid]);
    }

    public function getOneByUuid(string $userUuid): User
    {
        $row = $this->medoo->get(
            'user',
            '*',
            [
                'uuid' => $userUuid
            ]
        );

        return User::fromJson($row);
    }
}