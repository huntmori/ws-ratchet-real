<?php

namespace App\Request;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

class UserCreatePayload implements BasePayload
{
    use Buildable, ArraySerializable;

    #[FromArrayKey(key: 'id', required: true)]
    #[ToArrayKey(key: 'id')]
    public string $id;

    #[FromArrayKey(key: 'password', required: true)]
    #[ToArrayKey(key: 'password')]
    public string $password;

}