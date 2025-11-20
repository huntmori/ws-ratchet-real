<?php

namespace App\Exception;

use App\Response\BaseResponse;

class ApiException extends \Exception
{
    public function toResponse(): BaseResponse
    {
        return new BaseResponse()
            ->success(false)
            ->message($this->getMessage())
            ->eventName('error')
            ->code($this->getCode())
            ->data([])
            ->build();
    }
}