<?php

namespace App\Controller;

use App\ConnectionPair;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class ChatController implements MessageComponentInterface
{
    /** @var array<int, ConnectionPair> $connections */
    private array $connections = [];
    private ?LoggerInterface $logger = null;
    public function __construct(ContainerInterface $c)
    {
        $this->logger = $c->get(LoggerInterface::class);
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

            // 다른 모든 클라이언트에게 메시지 전송
            $sent = 0;
            foreach ($this->connections as $key => $value) {
                if ($key !== spl_object_id($from)) {
                    $value->connection->send($msg);
                    $sent++;
                }
            }

            $this->logger->info("{$sent}명에게 전송 완료");
        } catch (\Throwable $e) {
            $this->logger->error("onMessage 오류: " . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }
}