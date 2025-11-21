<?php

namespace App\Model;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

/**
 * @method User id(string $id):
 * @method User uuid(string $uuid)
 * @method User password(string $password)
 * @method User createdAt(\DateTime $createdAt)
 */
class User extends BaseModel
{
    use Buildable,
        ArraySerializable;

    #[ToArrayKey(key: 'idx')]
    public ?int $idx = null;

    #[ToArrayKey(key: 'uuid', exclude: false)]
    #[FromArrayKey(key: 'uuid', required: false)]
    public ?string $uuid = null;

    #[ToArrayKey(key: 'id', exclude: false)]
    #[FromArrayKey(key: 'id', required: false)]
    public ?string $id = null;

    #[ToArrayKey(key: 'password', exclude: true)]
    #[FromArrayKey(key: 'password', required: false)]
    public ?string $password = null;

    #[ToArrayKey(key: 'created_at', exclude: false)]
    #[FromArrayKey(key: 'created_at', required: false)]
    public ?\DateTime $createdAt = null;
}