<?php
declare(strict_types=1);

namespace Simy\Core;

use Simy\Core\Psr\Http\Message\ResponseInterface;

class ResponseFactory
{
    public static function make($response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        if (is_string($response)) {
            return new Response($response);
        }

        if (is_array($response)) {
            return Response::json($response);
        }

        if ($response === null) {
            return new Response('', 204);
        }

        throw new \RuntimeException('Cannot convert response to ResponseInterface');
    }
}