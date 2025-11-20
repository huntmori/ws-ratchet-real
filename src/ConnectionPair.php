<?php

namespace App;

use App\Model\User;
use Ratchet\ConnectionInterface;

class ConnectionPair
{
    public ConnectionInterface $connection;
    public ?User $profile = null;
}