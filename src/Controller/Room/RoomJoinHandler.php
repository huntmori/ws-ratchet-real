<?php

namespace App\Controller\Room;

use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Enum\InRoomStatus;
use App\Exception\ApiException;
use App\Model\UsersInRoom;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\UsersInRoomRepository;
use App\Request\BasePayload;
use App\Request\BaseRequest;
use App\Request\Room\RoomJoinPayload;
use App\Response\BaseResponse;
use App\RoomUserPair;
use http\Message;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

final readonly class RoomJoinHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
        private UsersInRoomRepository $usersInRoomRepository,
        private UserRepository $userRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws ApiException
     */
    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        $this->logger->debug(__METHOD__ . ' is called. ');

        $base = BaseRequest::fromJson($data);
        $base->payload = RoomJoinPayload::fromJson($data['payload']);

        // select room
        $room = $this->roomRepository->getOneByUuid($base->payload->roomUuid());
        // password check
        if($room->joinPassword() !== $base->payload->roomPassword())
        {
            throw new ApiException(
                message: 'password not matched',
                code: -1
            );
        }

        $key = spl_object_id($from);
        $userPair = $chatController->connections[$key];
        $user = $userPair->profile;

        if($user === null)
        {
            throw new ApiException(
                message: 'please login first',
                code: -1
            );
        }
        // TODO: exist check

        // insert users in room
        $inRoom = UsersInRoom::builder()
            ->userUuid($user->uuid())
            ->roomUuid($room->uuid())
            ->state(InRoomStatus::JOIN)
            ->build();
        $inRoom = $this->usersInRoomRepository->save($inRoom);

        if(!$inRoom)
        {
            throw new ApiException(
                message: 'error while joining',
                code: -1
            );
        }

        $response = BaseResponse::builder()
            ->success(true)
            ->data($room)
            ->build();
        $from->send($response->toJson());
    }

    public function getEventName(): string
    {
        return RoomJoinPayload::EVENT_NAME;
    }
}