<?php

namespace App\Request\User;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Request\BasePayload;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

class UserLoginPayload implements BasePayload
{
    use Buildable, ArraySerializable;

    public const string EVENT_NAME = "user.login";

    #[FromArrayKey(key: 'id', required: true)]
    #[ToArrayKey(key: 'id')]
    public string $id;

    #[FromArrayKey(key: 'password', required: true)]
    #[ToArrayKey(key: 'password')]
    public string $password;

}