<?php

namespace App;

use Ratchet\ConnectionInterface;

class ConnectionPair
{
    public ConnectionInterface $connection;
    public ?object $profile = null;
}