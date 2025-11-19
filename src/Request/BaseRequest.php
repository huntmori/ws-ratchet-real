<?php

namespace App\Request;

use App\Attribute\ArrayKeyIgnore;
use App\Attribute\FromArrayKey;

class BaseRequest
{
    #[FromArrayKey(key: 'event_name', required: true)]
    public string $eventName;

    #[ArrayKeyIgnore]
    public ?BasePayload $payload;
}