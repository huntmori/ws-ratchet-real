<?php

namespace App\Attribute;

use Attribute;

/**
 * ToArrayKey 애트리뷰트
 *
 * 객체를 배열로 변환할 때 사용할 배열 키를 지정하는 애트리뷰트
 * 프로퍼티와 배열 키의 이름이 다를 때 매핑을 위해 사용됩니다.
 *
 * @property string $key 배열에 저장할 키 이름
 * @property bool $exclude 배열 변환 시 제외 여부 (기본값: false)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class ToArrayKey
{
    public function __construct(
        public string $key,              // 배열로 변환 시 사용할 키 이름
        public bool   $exclude = false   // 배열 변환에서 제외할지 여부
    ) {}
}