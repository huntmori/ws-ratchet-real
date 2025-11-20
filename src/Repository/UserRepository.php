<?php

namespace App\Repository;

use App\Model\User;
use Medoo\Medoo;
use Psr\Container\ContainerInterface;

class UserRepository extends BaseRepository
{
    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
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
                'created_at' => $user->createdAt === null ?: Medoo::raw('now()')
            ]
        );
        $idx = (int)$this->medoo->pdo->lastInsertId();
        return $this->getOneByIdx($idx);
    }

    private function update(User $user): ?User
    {
        return null;
    }
}