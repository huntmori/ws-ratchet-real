<?php

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class ToArrayKey
{
    public function __construct(
        public string $key,
        public bool   $exclude = false
    ) {}
}