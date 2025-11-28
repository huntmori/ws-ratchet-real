<?php

namespace App\Controller;

use App\ConnectionPair;
use App\Controller\Room\RoomChatHandler;
use App\Controller\Room\RoomCreateHandler;
use App\Controller\Room\RoomJoinHandler;
use App\Controller\User\UserCreateHandler;
use App\Controller\User\UserLoginHandler;
use App\Handler\PredisHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * ChatController 클래스
 *
 * WebSocket 메시지를 처리하는 메인 컨트롤러
 * Ratchet의 MessageComponentInterface를 구현하여 WebSocket 이벤트를 처리합니다.
 *
 * 주요 기능:
 * - 클라이언트 연결/종료 관리
 * - 메시지 수신 및 라우팅
 * - 연결된 사용자 관리
 * - 에러 처리
 */
final class ChatController implements MessageComponentInterface
{
    /** @var array<int, ConnectionPair> $connections 연결된 클라이언트 목록 */
    public array $connections = [];

    ///** @var array<string, User> */
    //public array $users = [];

    private ?LoggerInterface $logger = null;        // 로거
    private ?RequestDispatcher $dispatcher = null;  // 요청 디스패처
    private ?ContainerInterface $container = null;  // DI 컨테이너
    private ?PredisHandler $redisHandler = null;    // Redis 핸들러
    public function __construct(
        ContainerInterface $c,
        LoggerInterface $logger,
        RequestDispatcher $dispatcher,
        PredisHandler $redisHandler
    ) {
        $this->logger = $logger;
        $this->container = $c;
        $this->dispatcher = $dispatcher;
        $this->redisHandler = $redisHandler;
    }

    function onOpen(ConnectionInterface $conn): void
    {
        $this->logger->info("=== 새 연결 ===");
        $this->logger->info("ID: " . spl_object_id($conn));
        $this->logger->info("주소: " . $conn->remoteAddress);

        try {
            $pair = new ConnectionPair($conn, null);
            $pair->connection = $conn;
            $this->connections[spl_object_id($conn)] = $pair;

            $this->logger->info("연결 성공! (총 " . count($this->connections) . "개)");

            // 연결 성공 메시지 전송
            $conn->send(json_encode([
                'type' => 'connection',
                'message' => '연결 성공!',
                'id' => spl_object_id($conn),
                'total' => count($this->connections)
            ]));

            $this->logger->info("환영 메시지 전송 완료");
        } catch (\Throwable $e) {
            $this->logger->error("onOpen 오류: " . $e->getMessage());
            $this->logger->error("파일: " . $e->getFile() . ":" . $e->getLine());
            $this->logger->error($e->getTraceAsString());
            $conn->close();
        }
    }

    function onClose(ConnectionInterface $conn): void
    {
        $this->logger->info("=== 연결 종료 ===");
        $this->logger->info("ID: " . spl_object_id($conn));
        unset($this->connections[spl_object_id($conn)]);
        $this->logger->info("남은 연결: " . count($this->connections) . "개");
    }

    function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $this->logger->error("=== WebSocket 오류 ===");
        $this->logger->error("ID: " . spl_object_id($conn));
        $this->logger->error("오류: " . $e->getMessage());
        $this->logger->error("파일: " . $e->getFile() . ":" . $e->getLine());
        $this->logger->error($e->getTraceAsString());
    }

    function onMessage(ConnectionInterface $from, $msg): void
    {
        try {
            $this->logger->info("=== 메시지 수신 ===");
            $this->logger->info("발신자: " . spl_object_id($from));
            $this->logger->info("내용: " . $msg);

            $decode = json_decode($msg, true);

            $this->dispatcher->dispatch($from, $msg, $this);
        } catch (\Throwable $e) {
            $this->logger->error("onMessage 오류: " . $e->getMessage(), [$e->getLine()]);
            $this->logger->error($e->getTraceAsString());
        }
    }

    public function registerDispatchers(): self
    {
        $this->dispatcher
            ->registerHandler($this->container->get(UserCreateHandler::class))
            ->registerHandler($this->container->get(UserLoginHandler::class))
            ->registerHandler($this->container->get(RoomCreateHandler::class))
            ->registerHandler($this->container->get(RoomJoinHandler::class))
            ->registerHandler($this->container->get(RoomChatHandler::class));
        return $this;
    }
}