<?php
declare(strict_types=1);

namespace Simy\Core\Exceptions;

use Simy\Core\Config;
use Simy\Core\Response;
use Throwable;

class ErrorHandler
{
    private bool $debug;
    private string $logFile;

    public function __construct(bool $debug = false,?string $logFile = null)
    {
        $this->debug = $debug;
        $this->logFile = $logFile ?? dirname(__DIR__, 2) . '/storage/logs/error.log';
    }

    public function handle(Throwable $e): void
    {
        $this->logError($e);
        $response = $this->createResponse($e);
        $response->send();
    }

    private function logError(Throwable $e): void
    {
        $logEntry = sprintf(
            "[%s] %s: %s in %s:%d\nStack Trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    private function createResponse(Throwable $e): Response
    {
        if ($e instanceof HttpException) {
            return $this->createHttpResponse($e);
        }

        return $this->debug 
            ? $this->createDebugResponse($e)
            : $this->createProductionResponse();
    }

    private function createHttpResponse(HttpException $e): Response
    {
        $message = $this->debug 
            ? $e->getMessage() 
            : $this->getHttpStatusMessage($e->getStatusCode());

        return new Response($message, $e->getStatusCode());
    }

    private function createDebugResponse(Throwable $e): Response
    {
        $content = sprintf(
            "Error: %s\n\nFile: %s (Line %d)\n\nStack Trace:\n%s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        return new Response($content, 500, ['Content-Type' => 'text/plain']);
    }

    private function createProductionResponse(): Response
    {
        return new Response(
            "An error occurred. Our team has been notified.",
            500,
            ['Content-Type' => 'text/plain']
        );
    }

    private function getHttpStatusMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error'
        ];

        return $messages[$statusCode] ?? 'Unknown Error';
    }
}