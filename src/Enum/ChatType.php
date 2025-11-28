<?php

namespace App\Enum;

/**
 * ChatType Enum
 *
 * 채팅 메시지의 종류를 정의하는 열거형
 *
 * @property string SIMPLE_TEXT 단순 텍스트 메시지
 */
enum ChatType: string
{
    case SIMPLE_TEXT = "SIMPLE_TEXT";  // 일반 텍스트 메시지
}