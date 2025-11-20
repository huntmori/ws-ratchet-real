<?php

namespace App\Request;

use App\Attribute\ArrayKeyIgnore;
use App\Attribute\FromArrayKey;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

class BaseRequest
{
    use Buildable, ArraySerializable;
    #[FromArrayKey(key: 'event_name', required: true)]
    public string $eventName;

    #[ArrayKeyIgnore]
    public ?BasePayload $payload;
}