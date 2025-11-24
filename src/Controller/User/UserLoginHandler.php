<?php

namespace App\Controller\User;

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
        private UsersInRoomRepository $usersInRoomRepository,
        private RoomRepository $roomRepository,
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

        $chatController->connections[spl_object_id($from)]->profile = $user;
        $this->logger->debug('type of connection is : ', [gettype($from), get_class($from)]);
        $this->logger->debug('get called class : ', [get_called_class()]);

        // 참가중인 방을 select하여 메모리에 set
        $rooms = [];
        // 참여 중인 방 정보 select
        $usersInRoom = $this->usersInRoomRepository->getListByUserUuid($user->uuid);
        $this->logger->debug('usersInRoom : ', [$usersInRoom]);

        $rooms = $this->roomRepository->getListByRoomUuid(
            array_map(fn($row) => $row->roomUuid, $usersInRoom)
        );

        // 룸 유저 select
        for($i=0; $i<count($rooms); $i++)
        {
            $roomUuid = $rooms[$i]->uuid;

            $users = $this->repository->getListByRoomUuid($roomUuid);
            $sessionKey = RoomUserPair::getSessionKeyByUuid($roomUuid);

            if(!array_key_exists($sessionKey, $chatController->rooms))
            {
                $pair = RoomUserPair::builder()
                    ->room($rooms[$i])
                    ->users($users)
                    ->messages([])
                    ->build();
                $chatController->rooms[$sessionKey] = $pair;
            }
        }

        $from->send($user->toJson());
    }

    public function getEventName(): string
    {
        return UserLoginPayload::EVENT_NAME;
    }
}