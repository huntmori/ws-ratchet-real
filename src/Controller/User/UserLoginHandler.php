<?php

namespace App\Controller\User;

use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Exception\ApiException;
use App\Repository\UserRepository;
use App\Request\BaseRequest;
use App\Request\User\UserCreatePayload;
use App\Request\User\UserLoginPayload;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

readonly class UserLoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserRepository  $repository,
        private LoggerInterface $logger
    ) {}

    /**
     * @throws ApiException
     */
    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        // dto 변ㄴ환
        $decoded = $data;
        $baseRequest = BaseRequest::fromJson($data);
        $baseRequest->payload = UserLoginPayload::fromJson($decoded['payload']);

        // id 존재 확인
        $idExists = true;
        $idExists = $this->repository->existsById($baseRequest->payload->id);

        // password 일치 확인
        $user = null;
        $passwordMatch = false;
        if($idExists) {
            $user = $this->repository->getOneById($baseRequest->payload->id);

            if($user->password !== $baseRequest->payload->password) {
                $passwordMatch = true;
            }
        }

        // 오류처리
        if(($passwordMatch && $idExists) === false) {
            throw new ApiException(
                message: 'please check you id or password',
                code: 100001
            );
        }

        $chatController->connections[spl_object_id($from)]->profile = $user;

        $from->send($user->toJson());
    }

    public function getEventName(): string
    {
        return UserLoginPayload::EVENT_NAME;
    }
}