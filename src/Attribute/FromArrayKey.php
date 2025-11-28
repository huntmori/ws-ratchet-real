<?php

namespace App\Attribute;

use Attribute;

/**
 * FromArrayKey 애트리뷰트
 *
 * 배열에서 객체로 변환할 때 사용할 배열 키를 지정하는 애트리뷰트
 * 프로퍼티와 배열 키의 이름이 다를 때 매핑을 위해 사용됩니다.
 *
 * @property string $key 배열에서 읽을 키 이름
 * @property bool $required 필수 항목 여부 (기본값: false)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class FromArrayKey
{
    public function __construct(
        public readonly string $key,      // 배열에서 읽어올 키 이름
        public readonly bool $required = false  // 필수 항목 여부
    ) {}
}