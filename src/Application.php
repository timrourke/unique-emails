<?php

declare(strict_types=1);

namespace UniqueEmails;

use FastRoute\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;

final class Application
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handleRequest(ServerRequestInterface $request, string $uri): void
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->renderNotFound();
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->renderMethodNotAllowed();
                break;
            default:
                $this->handleUniqueEmailRequest($request);
        }
    }

    private function handleUniqueEmailRequest(ServerRequestInterface $request): void
    {
        try {
            header("Content-type: application/json");
            echo json_encode(UniqueEmailsResponse::fromRequest($request));
        } catch (InvalidRequestException $e) {
            http_response_code(400);
            echo json_encode([
                'error' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function renderNotFound(): void
    {
        http_response_code(404);
        echo 'Not Found';
    }

    private function renderMethodNotAllowed(): void
    {
        http_response_code(405);
        echo 'Method Not Allowed';
    }
}
