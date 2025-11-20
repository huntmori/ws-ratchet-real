<?php

namespace App\Controller;

use App\Application;
use App\Model\User;
use App\Repository\UserRepository;
use App\Request\BaseRequest;
use App\Request\UserCreatePayload;
use App\Response\BaseResponse;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Ratchet\App;
use Ratchet\ConnectionInterface;

class UserCreateHandler implements RequestHandlerInterface
{
    public const string EVENT_NAME = "user.create";
    private readonly ?LoggerInterface $logger;
    private readonly UserRepository $repository;
    public readonly ChatController $chatController;

    public function __construct(
        LoggerInterface $logger,
        UserRepository $repository,
        ChatController $chatController
    ) {
        $this->logger = $logger;
        $this->repository = $repository;
        $this->chatController = $chatController;
    }


    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        // TODO: Implement handle() method.
        // RequestDto 변환
        $decoded = $data;
        $baseRequest = BaseRequest::fromJson($data);
        $baseRequest->payload = UserCreatePayload::fromJson($decoded['payload']);
        $this->logger->info("Event [{$baseRequest->eventName}] 's payload : ", $baseRequest->payload->toArray());
        // TODO:유효성 검사
        // Model생성
        $model = User::builder()
            ->id($baseRequest->payload->id)
            ->password($baseRequest->payload->password)
            ->build();

        // Repo->insert
        $user = $this->repository->save($model);
        $this->logger->info('user create : ' , $user->toArray());

        // 결과-> dto
        /* @var BaseResponse $response */
        $response = BaseResponse::builder()
            ->success(true)
            ->eventName("user.create.result")
            ->message("ok")
            ->data($user->toArray())
            ->code(200)
            ->build();

        // 메모리 set
        $objectId = spl_object_id($from);
        $this->logger->info('connections: ', $chatController->connections);
        $chatController->connections[$objectId]->profile = $user;
        $this->logger->info('after connections: ', $chatController->connections);

        $from->send(json_encode($response->toArray()));
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }
}