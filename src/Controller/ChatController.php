<?php

namespace App\Controller;

use App\ConnectionPair;
use App\Controller\Room\RoomCreateHandler;
use App\Controller\Room\RoomJoinHandler;
use App\Controller\User\UserCreateHandler;
use App\Controller\User\UserLoginHandler;
use App\Model\Room;
use App\Model\User;
use App\Request\Room\RoomCreatePayload;
use App\RoomUserPair;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

final class ChatController implements MessageComponentInterface
{
    /** @var array<int, ConnectionPair> $connections */
    public array $connections = [];

    /** @var array<string, RoomUserPair> */
    public array $rooms = [];

    /** @var array<string, int> : 유저아이디로 커넥션 pair를 찾을 수 있도록 하기 위함 */
    public array $userUuidToConnectionId = [];
    private ?LoggerInterface $logger = null;

    private ?RequestDispatcher $dispatcher = null;
    private ?ContainerInterface $container = null;
    public function __construct(ContainerInterface $c, LoggerInterface $logger, RequestDispatcher $dispatcher)
    {
        $this->logger = $logger;
        $this->container = $c;
        $this->dispatcher = $dispatcher;
    }

    function onOpen(ConnectionInterface $conn): void
    {
        $this->logger->info("=== 새 연결 ===");
        $this->logger->info("ID: " . spl_object_id($conn));
        $this->logger->info("주소: " . $conn->remoteAddress);

        try {
            $pair = new ConnectionPair();
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
            $this->logger->error("onMessage 오류: " . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }

    public function registerDispatchers(): self
    {
        $this->dispatcher
            ->registerHandler($this->container->get(UserCreateHandler::class))
            ->registerHandler($this->container->get(UserLoginHandler::class))
            ->registerHandler($this->container->get(RoomCreateHandler::class))
            ->registerHandler($this->container->get(RoomJoinHandler::class));
        return $this;
    }

    public function setRoomPair(?Room $room, User $user, ?ConnectionInterface $connection): RoomUserPair
    {
        $sessionKey = RoomUserPair::getSessionKeyByUuid($room->uuid);

        if($connection !== null) {
            $this->userUuidToConnectionId[$user->uuid] = spl_object_id($connection);
            $user->connection = $connection;
        }

        $this->logger->debug('rooms :', $this->rooms);
        if(!array_key_exists($sessionKey, $this->rooms))
        {
            /** @var RoomUserPair $pair */
            $pair = RoomUserPair::builder()
                ->room($room)
                ->users([])
                ->messages([])
                ->build();
            $pair->users[] = $user;

            $this->rooms[$sessionKey] = $pair;
        } else {
            $this->rooms[$sessionKey]->users[] = $user;
        }

        return $this->rooms[$sessionKey];
    }
}