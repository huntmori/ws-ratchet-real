<?php

namespace App\Controller;

use App\Exception\ApiException;
use App\Request\BaseRequest;
use App\Response\BaseResponse;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

class RequestDispatcher
{
    /** @var array<string, RequestHandlerInterface> $handlers */
    private array $handlers = [];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function registerHandler(RequestHandlerInterface $handler): self
    {
        $this->logger->info('register handler : ' . $handler->getEventName());
        $this->handlers[$handler->getEventName()] = $handler;

        return $this;
    }
    public function dispatch(ConnectionInterface $from, string $message, ChatController $chatController): void
    {
        $this->logger->info('message Coming : ' . $message);

        try {
            $data = json_decode($message, true);

            $request = BaseRequest::fromJson($message);
            $eventType = $request->eventName;
            if (!isset($this->handlers[$request->eventName])) {
                throw new ApiException("Unknown command: {$eventType}", 400);
            }

            $this->handlers[$eventType]->handle($from, $data, $chatController);
        } catch (ApiException $e) {
            $response = $e->toResponse();
            $from->send($response->toJson());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}