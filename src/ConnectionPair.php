<?php

namespace App;

use App\Model\User;
use Ratchet\ConnectionInterface;

class ConnectionPair
{
    public ConnectionInterface $connection;
    public ?User $profile = null;

    public function __construct(ConnectionInterface $con, ?User $user)
    {
        $this->connection = $con;
        $this->profile = $user;
    }
}