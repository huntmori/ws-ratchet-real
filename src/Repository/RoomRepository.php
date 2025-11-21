<?php

namespace App\Repository;

use Medoo\Medoo;
use Psr\Log\LoggerInterface;

class RoomRepository extends BaseRepository
{
    public function __construct(Medoo $medoo, LoggerInterface $logger)
    {
        parent::__construct($medoo, $logger);
    }
}