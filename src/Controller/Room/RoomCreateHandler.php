<?php

namespace App\Controller\Room;

use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Enum\InRoomStatus;
use App\Exception\ApiException;
use App\Model\Room;
use App\Model\User;
use App\Model\UsersInRoom;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\UsersInRoomRepository;
use App\Request\BaseRequest;
use App\Request\Room\RoomCreatePayload;
use App\Request\User\UserCreatePayload;
use App\Response\RoomCreateResponse;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

readonly class RoomCreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RoomRepository $roomRepository,
        private readonly UserRepository $userRepository,
        private readonly UsersInRoomRepository $usersInRoomRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws ApiException
     */
    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        $this->logger->debug(__METHOD__. " is called");;
        // TODO: Implement handle() method.
        // dto 변환
        $baseRequest = BaseRequest::fromJson($data);
        $payload = $baseRequest->payload = RoomCreatePayload::fromJson($data['payload']);
        // 로그인 체크
        $key = spl_object_id($from);
        $connectionPair = $chatController->connections[$key];
        if($connectionPair->profile === null) {
            throw new ApiException(
                message: 'please login first',
                code: -1
            );
        }

        /** @var ?User $owner */
        $owner = $connectionPair->profile;

        // room create
        $room = Room::builder()
            ->roomName($payload->roomName)
            ->maximumUsers($payload->maximumUsers)
            ->joinType($payload->joinType)
            ->openType($payload->openType)
            ->joinPassword($payload->joinPassword)
            ->build();
        // room insert
        $room = $this->roomRepository->save($room);
        $this->logger->info("Room created with ID: " . $room->uuid);


        // room join
        $join = UsersInRoom::builder()
            ->userUuid($owner->uuid)
            ->roomUuid($room->uuid)
            ->state(InRoomStatus::JOIN)
            ->build();
        $inRoom = $this->usersInRoomRepository->save($join);

        // send dto
        $dto = new RoomCreateResponse();
        $dto->room = $room;
        $dto->users[] = $owner;

        $from->send(
            json_encode(
                $dto->toArray()
            )
        );
    }

    public function getEventName(): string
    {
        return RoomCreatePayload::EVENT_NAME;
    }
}