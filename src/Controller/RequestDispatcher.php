<?php

namespace App\Controller;

use App\Exception\ApiException;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

class RequestDispatcher
{
    /** @var array<string, RequestHandlerInterface> $handlers */
    private array $handlers = [];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function registerHandler(RequestHandlerInterface $handler): void
    {
        $this->handlers[$handler->getEventName()] = $handler;
    }
    public function dispatch(ConnectionInterface $from, string $message): void
    {
        $this->logger->info('message Coming : ' . $message);

        try {
            $data = json_decode($message, true);
            $command = $data['event_name'] ?? 'empty';

            if (!isset($this->handlers[$command])) {
                throw new ApiException("Unknown command: {$command}", 400);
            }

            $this->handlers[$command]->handle($from, $data);
        } catch (ApiException $e) {
            $response = $e->toResponse();
            $from->send($response->toJson());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}