<?php

namespace App\Controller\Room;

use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Enum\InRoomStatus;
use App\Enum\JoinType;
use App\Exception\ApiException;
use App\Handler\PredisHandler;
use App\Model\UsersInRoom;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\UsersInRoomRepository;
use App\Request\BaseRequest;
use App\Request\Room\RoomJoinPayload;
use App\Response\BaseResponse;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

final readonly class RoomJoinHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
        private UsersInRoomRepository $usersInRoomRepository,
        private UserRepository $userRepository,
        private PredisHandler $redis,
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
        if(
            $room->joinPassword() !== $base->payload->roomPassword()
            && $room->joinType === JoinType::PASSWORD
        ) {
            throw new ApiException(
                message: 'password not matched',
                code: -1
            );
        }

        $key = spl_object_id($from);
        $userUuid = $this->redis->getUserUuidByConnectionId($key);

        if($userUuid === null) {
            throw new ApiException(message: 'please login first',code:-1);
        }
        $user = $this->userRepository->getOneByUuid($userUuid);

        // Check if user already joined the room
        $alreadyJoined = $this->usersInRoomRepository->hasByRoomUuidAndUserUuid($room->uuid(), $user->uuid());
        $this->logger->info('already joined ? : ', [$alreadyJoined]);
        if($alreadyJoined) {
            throw new ApiException(
                message: 'already in room',code: -1
            );
        }

        //현재 인원과 max인원 체크
        $currentUsers = $this->usersInRoomRepository->countByRoomUuid($room->uuid());
        if($currentUsers >= $room->maximumUsers) {
            throw new ApiException(
                message: 'room is full',
                code: -1
            );
        }

        // insert users in room
        $inRoom = UsersInRoom::builder()
            ->userUuid($user->uuid())
            ->roomUuid($room->uuid())
            ->state(InRoomStatus::JOIN)
            ->build();
        $inRoom = $this->usersInRoomRepository->save($inRoom);

        if(!$inRoom) {
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