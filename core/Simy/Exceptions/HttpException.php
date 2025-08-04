<?php
declare(strict_types = 1);

namespace Simy\Core\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpException extends \RuntimeException
{
  private int $statusCode;
  private array $headers;

  public function __construct(
    ?string $message = '',
    int $statusCode = 500,
    array $headers = [],
    ?Throwable $previous = null
  ) {
    parent::__construct($message, $statusCode, $previous);
    $this->statusCode = $statusCode;
    $this->headers = $headers;
  }

  public function getStatusCode(): int
  {
    return $this->statusCode;
  }

  public function getHeaders(): array
  {
    return $this->headers;
  }
}