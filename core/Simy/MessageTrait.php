<?php
declare(strict_types=1);

namespace Simy\Core;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

trait MessageTrait
{
    private string $protocolVersion = '1.1';
    private array $headers = [];
    private StreamInterface $stream;

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): MessageInterface
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader($name): array
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): MessageInterface
    {
        $normalized = strtolower($name);
        $new = clone $this;
        
        if (isset($new->headers[$normalized])) {
            unset($new->headers[$normalized]);
        }
        
        $new->headers[$normalized] = is_array($value) ? $value : [$value];
        return $new;
    }

    public function withAddedHeader($name, $value): MessageInterface
    {
        $normalized = strtolower($name);
        $new = clone $this;
        $new->headers[$normalized] = array_merge(
            $this->getHeader($name),
            is_array($value) ? $value : [$value]
        );
        return $new;
    }

    public function withoutHeader($name): MessageInterface
    {
        $normalized = strtolower($name);
        $new = clone $this;
        
        if (isset($new->headers[$normalized])) {
            unset($new->headers[$normalized]);
        }
        
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;
        return $new;
    }

    protected function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->headers[strtolower($name)] = is_array($value) ? $value : [$value];
        }
    }
}