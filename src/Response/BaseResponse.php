<?php
namespace App\Response;

use App\Attribute\ToArrayKey;
use App\Trait\Buildable;
use App\Trait\ArraySerializable;

/**
 * @method BaseResponse success(bool $success)
 * @method BaseResponse eventName(string $eventName)
 * @method BaseResponse message(string $message)
 * @method BaseResponse data(mixed $data)
 * @method BaseResponse code(int $code)
 * @method BaseResponse error(?string $error)
 * @method BaseResponse errorDescription(?string $errorDescription)
 */
class BaseResponse
{
    use Buildable;
    use ArraySerializable;

    #[ToArrayKey(key: 'success', exclude: false)]
    public bool $success;

    #[ToArrayKey(key: 'event_name', exclude: false)]
    public string $eventName = '';

    #[ToArrayKey(key: 'message', exclude: false)]
    public string $message;

    #[ToArrayKey(key: 'data', exclude: false)]
    public mixed $data;

    #[ToArrayKey(key: 'code', exclude: false)]
    public int $code;

    #[ToArrayKey(key: 'error', exclude: false)]
    public ?string $error;

    #[ToArrayKey(key: 'error_description', exclude: false)]
    public ?string $errorDescription;

}