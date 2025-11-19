<?php

namespace App\Repository;

use Medoo\Medoo;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class BaseRepository
{
    protected ?\Medoo\Medoo $medoo = null;
    protected ?LoggerInterface $logger = null;

    public function __construct(ContainerInterface $c)
    {
        $this->medoo = $c->get(Medoo::class);
        $this->logger = $c->get(LoggerInterface::class);
    }
}