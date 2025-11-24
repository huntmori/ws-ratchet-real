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

    protected ?LoggerInterface $logger = null;

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
        $this->logger = $logger;
        // set env
        $this->setEnv($containerBuilder);
        if(count($_ENV) > 0) {
            $logger->info('env 로드 완료');
            $logger->info('$_ENV[\'DB_HOST\'] : ', [$_ENV['DB_HOST']]);
        }

        // set db
        $medoo = $this->setMeedo($containerBuilder);

        if($medoo->info()) {
            $logger->info('Medoo 초기화 완료');
            $logger->info("test query", $medoo->pdo->query("select now()")->fetchAll());
        }

        // set redis
        $redis = $this->setRedis($containerBuilder);
        if($redis->info()) {
            $logger->info('redis client 초기화 완료');
        }

        $containerBuilder->useAutowiring(true);
        try {
            $this->container = $containerBuilder->build();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error("failed container build");
            exit(1);
        }
        $app = $this->setRatchet();

        $logger->info("WebSocket server started on 0.0.0.0:8888");

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

            // [optional]  // [optional]
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',  // 명시적으로 지정
            'port' => $_ENV['DB_PORT'],
            'logging'=>true,
            // PDO 옵션 추가 - 세션 레벨에서 collation 강제
            'option' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND =>
                    'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
            ]
        ]);

        $containerBuilder->addDefinitions([
            Medoo::class => $database
        ]);
        return $database;
    }

    private function setRedis(ContainerBuilder $containerBuilder): Client
    {
        $redis = new Client([
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
        $chat = $this->container->get(ChatController::class);
        $chat->registerDispatchers();

        $app = new App('localhost', 8888, '0.0.0.0', $this->eventLoop);
        $app->route('/chat', $chat, array('*'));

        $this->app = $app;
        return $this->app;
    }

    public function run(): void
    {
        $logger =  $this->logger;
        
        // 주기적인 로그
        $this->eventLoop->addPeriodicTimer(60, function() {
            /** @var LoggerInterface $logger */
            $logger = $this->container->get(LoggerInterface::class);

            $logger->info("=================================================================================");
            $logger->info("=================================================================================");
            $logger->info('connections : ', $this->container->get(ChatController::class)->connections);
            $logger->info("=================================================================================");
            $logger->info('rooms :', $this->container->get(ChatController::class)->rooms);
            $logger->info("=================================================================================");
            $logger->info("=================================================================================");
        });
        $logger->info('Ratchet 서버 초기화', [
            'host' => $this->host,
            'port' => $this->port
        ]);

        $logger->info('eventLoop ? ', [spl_object_id($this->eventLoop)]);
        $this->app->run();
    }
}