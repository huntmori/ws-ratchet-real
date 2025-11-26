<?php

namespace App\Controller\Room;

use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Enum\ChatType;
use App\Exception\ApiException;
use App\Model\RoomMessage;
use App\Repository\RoomMessageRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\UsersInRoomRepository;
use App\Request\BaseRequest;
use App\Request\Room\ChatPayload;
use App\Response\BaseResponse;
use App\RoomUserPair;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

readonly class RoomChatHandler implements RequestHandlerInterface
{
    public function __construct(
        private LoggerInterface       $logger,
        private UserRepository        $userRepository,
        private RoomRepository        $roomRepository,
        private RoomMessageRepository $roomMessageRepository,
        private UsersInRoomRepository $usersInRoomRepository
    ) {}
    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        $decoded = $data;
        $baseRequest = BaseRequest::fromJson($data);
        $baseRequest->payload = ChatPayload::fromJson($decoded['payload']);

        $roomUuid = $baseRequest->payload->roomUuid();
        $roomSessionKey = RoomUserPair::getSessionKeyByUuid($roomUuid);
        $userConnectionId = spl_object_id($from);

        $userUuid = $chatController->connections[$userConnectionId]->profile->uuid() ?? null;
        if ($userUuid === null) {
            $this->logger->warning('User UUID is null. Cannot send chat message.');
            throw new ApiException('User UUID is null. Cannot send chat message.');
        }

        // room uuid 가 유효한지 확인
        $exist = $this->roomRepository->existByUuid($roomUuid);
        if(!$exist) {
            $this->logger->warning('Room does not exist. Cannot send chat message.');
            throw new ApiException('Room does not exist. Cannot send chat message.');
        }

        // user uuid가 유효한지 확인
        $existUser = $this->userRepository->existsByUuid($userUuid);
        if(!$existUser) {
            $this->logger->warning('User does not exist. Cannot send chat message.');
            throw new ApiException('User does not exist. Cannot send chat message.');
        }

        // 해당 방에 참여중 인지 확인
        $isJoined = $this->usersInRoomRepository->existsByRoomUUidAndUserUuid($roomUuid, $userUuid);
        if(!$isJoined) {
            $this->logger->warning('User is not joined in the room. Cannot send chat message.');
            throw new ApiException('User is not joined in the room. Cannot send chat message.');
        }

        // message insert
        $message = RoomMessage::builder()
            ->roomUuid($roomUuid)
            ->userUuid($userUuid)
            ->type(ChatType::SIMPLE_TEXT)
            ->message($baseRequest->payload->message())
            ->build();
        $message = $this->roomMessageRepository->save($message);

        // Room 유저들 조회
        $users = $this->usersInRoomRepository->getListByRoomUuid($roomUuid);
        $this->logger->debug('room users', [$users]);

        $messagePayload = [
            'room_uuid'=> $roomUuid,
            'user_uuid'=> $userUuid,
            'message'=> $baseRequest->payload->message()
        ];
        $baseResponse = BaseResponse::builder()
            ->success(true)
            ->eventName('room.chat')
            ->data($messagePayload)
            ->build();

        $targetUserUuids = array_map(
            fn($e) => $e->userUuid(),
            $users
        );
        $this->logger->debug('target user uuids', [$targetUserUuids]);
        $filtered = [];
        foreach($chatController->connections as $connection) {
            $connectionUserUuid = $connection->profile->uuid() ?? null;
            if($connectionUserUuid !== null && in_array($connectionUserUuid, $targetUserUuids, true)) {
                $filtered[] = $connection;
            }
        }
        $this->logger->debug('filtered chat user', [$filtered]);

        foreach ($filtered as $pair) {
            $pair->connection->send($baseResponse->toJson());
        }
    }

    public function getEventName(): string
    {
        return ChatPayload::EVENT_NAME;
    }
}