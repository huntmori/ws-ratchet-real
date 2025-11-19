<?php

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromArrayKey
{
    public function __construct(
        public readonly string $key,
        public readonly bool $required = false
    ) {}
}