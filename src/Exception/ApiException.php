<?php

namespace App\Exception;

use App\Response\BaseResponse;

/**
 * ApiException 클래스
 *
 * API 요청 처리 중 발생하는 예외를 처리하는 커스텀 예외 클래스
 *
 * 주요 기능:
 * - 예외를 표준 응답 형식(BaseResponse)으로 변환
 * - 에러 메시지, 코드, 성공 여부를 포함한 구조화된 응답 제공
 * - WebSocket API의 일관된 에러 응답 형식 유지
 */
class ApiException extends \Exception
{
    /**
     * 예외를 BaseResponse 객체로 변환
     *
     * @return BaseResponse 에러 정보를 담은 응답 객체
     */
    public function toResponse(): BaseResponse
    {
        return new BaseResponse()
            ->success(false)                 // 성공 여부: false
            ->message($this->getMessage())   // 예외 메시지
            ->eventName('error')             // 이벤트명: error
            ->code($this->getCode())         // 예외 코드
            ->data([])                       // 추가 데이터: 빈 배열
            ->build();
    }
}