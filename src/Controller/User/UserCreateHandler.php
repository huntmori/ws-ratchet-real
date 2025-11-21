<?php

namespace App\Controller\User;

use App\Controller\ChatController;
use App\Controller\RequestHandlerInterface;
use App\Exception\ApiException;
use App\Model\User;
use App\Repository\UserRepository;
use App\Request\BaseRequest;
use App\Request\User\UserCreatePayload;
use App\Response\BaseResponse;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

final readonly class UserCreateHandler implements RequestHandlerInterface
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


    /**
     * @throws ApiException
     */
    public function handle(ConnectionInterface $from, $data, ChatController $chatController): void
    {
        // TODO: Implement handle() method.
        // RequestDto 변환
        $decoded = $data;
        $baseRequest = BaseRequest::fromJson($data);
        $baseRequest->payload = UserCreatePayload::fromJson($decoded['payload']);
        $this->logger->info("Event [{$baseRequest->eventName}] 's payload : ", $baseRequest->payload->toArray());

        // TODO:유효성 검사
        $exists = $this->repository->existsById($baseRequest->payload->id);
        if($exists) {
            throw new ApiException(
                message: 'this id is already exists. please use another different id',
                code: 100001,
            );
        }

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

        $from->send(json_encode($response->toArray()));
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }
}