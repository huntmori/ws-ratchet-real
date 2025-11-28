<?php

namespace App\Attribute;

use Attribute;

/**
 * ArrayKeyIgnore 애트리뷰트
 *
 * 배열 직렬화/역직렬화 시 해당 프로퍼티를 무시하도록 지정하는 애트리뷰트
 * 이 애트리뷰트가 적용된 프로퍼티는 toArray() 메서드와 fromArray() 메서드에서 제외됩니다.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayKeyIgnore
{
}