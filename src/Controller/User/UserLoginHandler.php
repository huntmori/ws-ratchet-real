<?php

namespace App\Controller\User;

use App\ConnectionPair;
use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Exception\ApiException;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\UsersInRoomRepository;
use App\Request\BaseRequest;
use App\Request\User\UserCreatePayload;
use App\Request\User\UserLoginPayload;
use App\RoomUserPair;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

final readonly class UserLoginHandler implements RequestHandlerInterface
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
        $this->logger->debug(__METHOD__. " is called");
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
            $this->logger->debug('$user->password : ', [$user->password]);
            $this->logger->debug('$baseRequest->payload->password : ', [$baseRequest->payload->password]);
            if($user->password === $baseRequest->payload->password) {
                $passwordMatch = true;
            }
        }

        $this->logger->info('passwordMatch: ', [$passwordMatch]);
        $this->logger->info('idExists: ', [$idExists]);

        // 오류처리
        if(($passwordMatch && $idExists) === false) {
            throw new ApiException(
                message: 'please check you id or password',
                code: -1
            );
        }

        // 로그인 성공처리, 커넥션 설정
        $connectionId = spl_object_id($from);
        $pair = new ConnectionPair($from,  $user);

        $chatController->connections[$connectionId] = $pair;
        $chatController->users[$user->uuid()] = $user;

        $from->send($user->toJson());
    }

    public function getEventName(): string
    {
        return UserLoginPayload::EVENT_NAME;
    }
}