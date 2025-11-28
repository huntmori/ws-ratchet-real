<?php

namespace App\Request;

use App\Attribute\ArrayKeyIgnore;
use App\Attribute\FromArrayKey;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

/**
 * BaseRequest 클래스
 *
 * 모든 WebSocket 요청의 기본 클래스
 * 클라이언트로부터 받은 요청 메시지를 구조화하여 처리합니다.
 *
 * @property string $eventName 이벤트명 (어떤 작업을 수행할지 정의)
 * @property BasePayload $payload 요청 데이터 페이로드
 */
class BaseRequest
{
    use Buildable, ArraySerializable;

    #[FromArrayKey(key: 'event_name', required: true)]
    public ?string $eventName = null;  // 이벤트 이름 (필수)

    #[ArrayKeyIgnore]  // 직렬화 제외
    public ?BasePayload $payload = null;  // 요청 페이로드 객체
}