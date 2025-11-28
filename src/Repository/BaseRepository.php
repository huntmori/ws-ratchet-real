<?php

namespace App\Repository;

use Medoo\Medoo;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseRepository 클래스
 *
 * 모든 Repository 클래스의 부모 클래스
 * 데이터베이스 연결(Medoo)과 로거를 공통으로 관리합니다.
 *
 * @property Medoo $medoo 데이터베이스 쿼리 빌더
 * @property LoggerInterface $logger 로깅 인터페이스
 */
class BaseRepository
{
    protected ?\Medoo\Medoo $medoo = null;      // 데이터베이스 쿼리 빌더 (Medoo)
    protected ?LoggerInterface $logger = null;  // PSR-3 로거 인터페이스

    /**
     * 생성자
     *
     * @param Medoo $medoo 데이터베이스 연결 객체
     * @param LoggerInterface $logger 로거 객체
     */
    public function __construct(Medoo $medoo, LoggerInterface $logger)
    {
        $this->medoo = $medoo;
        $this->logger = $logger;
    }
}