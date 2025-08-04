<?php
declare(strict_types=1);

namespace Simy\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    use MessageTrait;

    private int $statusCode;
    private string $reasonPhrase;

    public function __construct(
        $body = 'php://memory',
        int $status = 200,
        array $headers = []
    ) {
        $this->statusCode = $status;
        $this->stream = Stream::create($body);
        $this->setHeaders($headers);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->statusCode = (int)$code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public static function json(array $data, int $status = 200): self
    {
        $response = new self(json_encode($data), $status);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // In core/Response.php
public function send(): void
{
    // Clear any existing output
    if (ob_get_level() > 0) {
        ob_clean();
    }

    http_response_code($this->statusCode);
    
    foreach ($this->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header("$name: $value", false);
        }
    }
    
    echo $this->getBody()->getContents();
    exit;
}


}