<?php

namespace App;

use App\Controller\ChatController;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Ratchet\App;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

class Application
{
    public ?int $port = 8888;
    public ?string $host = null;

    protected ?LoopInterface $eventLoop = null;
    protected ?ContainerInterface $container = null;

    protected App $app;

    public function __construct()
    {
        $this->eventLoop = Loop::get();
    }

    public function bootstrap(): self
    {
        $containerBuilder = new ContainerBuilder();
        // set logger
        $logger = $this->setLogger($containerBuilder);
        $logger->info('logger 초기화 완료');
        // set env
        $this->setEnv($containerBuilder);
        if(count($_ENV) > 0) {
            $logger->info('env 로드 완료');
        }

        // set db
        $meedo = $this->setMeedo($containerBuilder);

        if($meedo->info()) {
            $logger->info('Medoo 초기화 완료');
        }

        // set redis
        $redis = $this->setRedis($containerBuilder);
        if($redis->info()) {
            $logger->info('redis client 초기화 완료');
        }

        $this->container = $containerBuilder->build();

        $app = $this->setRatchet();

        return $this;
    }

    private function setLogger(ContainerBuilder $containerBuilder): LoggerInterface
    {
        $logger = $logger = new \Monolog\Logger('app');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

        $containerBuilder->addDefinitions([
            LoggerInterface::class => function() use ($logger) {
                return $logger;
            }
        ]);

        return $logger;
    }

    private function setEnv(ContainerBuilder $containerBuilder): void
    {
        $dirname = dirname(__DIR__);
        $dotEnv = Dotenv::createImmutable($dirname);
        $dotEnv->safeLoad();
    }

    private function setMeedo(ContainerBuilder $containerBuilder): Medoo
    {
        $database = new Medoo([
            'type' => 'mysql',
            'host' => $_ENV['DB_HOST'],
            'database' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],

            // [optional]
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'port' => $_ENV['DB_PORT'],
        ]);

        $containerBuilder->addDefinitions([
            Medoo::class => $database
        ]);
        return $database;
    }

    private function setRedis(ContainerBuilder $containerBuilder): Client
    {
        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'],
            'port'   => $_ENV['REDIS_PORT'],
            'timeout' => 2.0,
        ]);
        $containerBuilder->addDefinitions([
            Client::class => $redis
        ]);

        return $redis;

    }

    private function setRatchet(): App
    {
        $logger =  $this->container->get(LoggerInterface::class);
        $logger->info('Ratchet 서버 초기화', [
            'host' => $this->host,
            'port' => $this->port
        ]);

        $logger->info('eventLoop ? ', [
            spl_object_id($this->eventLoop)
        ]);

        $chat = new ChatController($this->container);
        $app = new \Ratchet\App('localhost', 8888, '0.0.0.0', $this->eventLoop);
        $app->route('/chat', $chat, array('*'));

        $logger->info("WebSocket server started on 0.0.0.0:8888");
        $this->app = $app;
        return $this->app;
    }

    public function run(): void
    {
        $this->app->run();
    }
}