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

class UserLoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @throws ApiException
     */
    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        $decoded = $data;
        $baseRequest = BaseRequest::fromJson($data);
        $baseRequest->payload = UserLoginPayload::fromJson($decoded['payload']);

        $result = true;
        $exist = $this->repository->existsById($baseRequest->payload->id);

        if(!$exist) {
            $result = false;
        }

        $user = null;
        if($result) {
            $user = $this->repository->getOneById($baseRequest->payload->id);

            if($user->password !== $baseRequest->payload->password) {
                $result = false;
            }
        }

        if(!$result) {
            throw new ApiException(
                message: 'please check you id or password',
                code: 100001
            );
        }

        $chatController->connections[spl_object_id($from)]->profile = $user;
    }

    public function getEventName(): string
    {
        return UserLoginPayload::EVENT_NAME;
    }
}