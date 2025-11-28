<?php
namespace App\Response;

use App\Attribute\ToArrayKey;
use App\Trait\Buildable;
use App\Trait\ArraySerializable;

/**
 * BaseResponse 클래스
 *
 * 모든 WebSocket 응답의 기본 클래스
 * 클라이언트에게 보낼 응답 메시지를 구조화하여 생성합니다.
 *
 * @method BaseResponse success(bool $success)
 * @method BaseResponse eventName(string $eventName)
 * @method BaseResponse message(string $message)
 * @method BaseResponse data(mixed $data)
 * @method BaseResponse code(int $code)
 * @method BaseResponse error(?string $error)
 * @method BaseResponse errorDescription(?string $errorDescription)
 */
class BaseResponse
{
    use Buildable;
    use ArraySerializable;

    #[ToArrayKey(key: 'success', exclude: false)]
    public bool $success;  // 요청 성공 여부

    #[ToArrayKey(key: 'event_name', exclude: false)]
    public string $eventName = '';  // 이벤트명

    #[ToArrayKey(key: 'message', exclude: false)]
    public string $message;  // 응답 메시지

    #[ToArrayKey(key: 'data', exclude: false)]
    public mixed $data;  // 응답 데이터

    #[ToArrayKey(key: 'code', exclude: false)]
    public int $code;  // 응답 코드

    #[ToArrayKey(key: 'error', exclude: false)]
    public ?string $error;  // 에러 식별자

    #[ToArrayKey(key: 'error_description', exclude: false)]
    public ?string $errorDescription;  // 에러 상세 설명

}