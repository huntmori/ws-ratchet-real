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

        // session key 확인
        if(!array_key_exists($roomSessionKey, $chatController->rooms)) {
            $this->logger->warning('Room not found in chat controller. Cannot send chat message.');
            throw new ApiException('Room not found in chat controller. Cannot send chat message.');
        }

        // message insert
        $message = RoomMessage::builder()
            ->roomUuid($roomUuid)
            ->userUuid($userUuid)
            ->type(ChatType::SIMPLE_TEXT)
            ->message($baseRequest->payload->message())
            ->build();
        $message = $this->roomMessageRepository->save($message);

        // send messages
        $roomPair = $chatController->rooms[$roomSessionKey];
        $roomPair->messages[] = $message;

        for($i=0; $i<count($roomPair->users); $i++)
        {
            $connectionid = spl_object_id($roomPair->users[$i]->connection);
            if($userConnectionId !== $connectionid) {
                $roomPair->users[$i]->connection->send($message->toJson());
            }
        }
    }

    public function getEventName(): string
    {
        return ChatPayload::EVENT_NAME;
    }
}