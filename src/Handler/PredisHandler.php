<?php

namespace App\Handler;

use Predis\Client;
use Psr\Log\LoggerInterface;

/**
 * Predis Redis 클라이언트를 관리하는 핸들러 클래스
 */
final class PredisHandler
{
    public const int NOT_EXIST_KEY = -2;
    public const int NO_EXPIRE_TIME = -1;


    private Client $client;
    private ?LoggerInterface $logger;

    /**
     * @param Client $client Predis 클라이언트 인스턴스
     * @param LoggerInterface|null $logger 로거 인스턴스
     */
    public function __construct(Client $client, ?LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->logger = $logger;

        if ($this->logger) {
            $this->logger->info('PredisHandler 초기화 완료');
        }
    }

    /**
     * Redis 키의 값을 가져옵니다
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        try {
            $value = $this->client->get($key);
            return $value !== null ? (string) $value : null;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis GET 오류', ['key' => $key, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * Redis 키에 값을 설정합니다
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl 초 단위 만료 시간 (선택사항)
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        try {
            if ($ttl !== null) {
                $result = $this->client->setex($key, $ttl, $value);
            } else {
                $result = $this->client->set($key, $value);
            }

            return $result !== null;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis SET 오류', ['key' => $key, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * Redis 키를 삭제합니다
     *
     * @param string|array<string> $keys
     * @return int 삭제된 키의 개수
     */
    public function delete(string|array $keys): int
    {
        try {
            $result = $this->client->del($keys);
            return (int) $result;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis DEL 오류', ['keys' => $keys, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * Redis 키가 존재하는지 확인합니다
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        try {
            return (bool) $this->client->exists($key);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis EXISTS 오류', ['key' => $key, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * 해시 필드의 값을 가져옵니다
     *
     * @param string $key
     * @param string $field
     * @return string|null
     */
    public function hashGet(string $key, string $field): ?string
    {
        try {
            $value = $this->client->hget($key, $field);
            return $value !== null ? (string) $value : null;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis HGET 오류', ['key' => $key, 'field' => $field, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * 해시 필드에 값을 설정합니다
     *
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    public function hashSet(string $key, string $field, mixed $value): bool
    {
        try {
            $this->client->hset($key, $field, $value);
            return true;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis HSET 오류', ['key' => $key, 'field' => $field, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * 해시의 모든 필드와 값을 가져옵니다
     *
     * @param string $key
     * @return array<string, string>
     */
    public function hashGetAll(string $key): array
    {
        try {
            return $this->client->hgetall($key);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis HGETALL 오류', ['key' => $key, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * 키의 만료 시간을 설정합니다
     *
     * @param string $key
     * @param int $seconds
     * @return bool
     */
    public function setExpire(string $key, int $seconds): bool
    {
        try {
            return (bool) $this->client->expire($key, $seconds);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis EXPIRE 오류', ['key' => $key, 'seconds' => $seconds, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * 키의 남은 만료 시간을 가져옵니다 (초 단위)
     *
     * @param string $key
     * @return int -2: 키가 없음, -1: 만료 시간이 없음, 그 외: 남은 초
     */
    public function getTtl(string $key): int
    {
        try {
            return (int) $this->client->ttl($key);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Redis TTL 오류', ['key' => $key, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * Predis 클라이언트 인스턴스를 반환합니다
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Redis 연결을 종료합니다
     */
    public function disconnect(): void
    {
        try {
            $this->client->disconnect();
            if ($this->logger) {
                $this->logger->info('Predis 연결 종료');
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Predis 연결 종료 오류', ['error' => $e->getMessage()]);
            }
        }
    }

    public function setConnectionIdToUserUuid(int $connectionId, string $userUuid): void
    {
        $this->set(
            "connection:$connectionId",
            $userUuid,
            self::NO_EXPIRE_TIME
        );
    }

    public function setUserUuidToConnectionId(string $userUuid, int $connectionId): void
    {
        $this->set(
            "user:$userUuid",
            $connectionId,
            self::NO_EXPIRE_TIME
        );
    }

    public function getConnectionIdByUserUuid(string $userUuid): ?string
    {
        return $this->get("user:$userUuid");
    }

    public function getUserUuidByConnectionId(int $connectionId): int
    {
        return (int)$this->get("connection:$connectionId");
    }
}
