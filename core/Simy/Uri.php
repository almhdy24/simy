<?php
declare(strict_types = 1);

namespace Simy\Core;

use Simy\Core\Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
  private string $scheme = '';
  private string $userInfo = '';
  private string $host = '';
  private ?int $port = null;
  private string $path = '';
  private string $query = '';
  private string $fragment = '';

  public static function createFromServer(array $server): self
{
    $scheme = isset($server['HTTPS']) && $server['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost';
    $port = null;
    
    // Handle port in HTTP_HOST (e.g., "localhost:8080")
    if (str_contains($host, ':')) {
        [$host, $portStr] = explode(':', $host, 2);
        $port = (int)$portStr;
    }
    
    // Fallback to SERVER_PORT
    if ($port === null && isset($server['SERVER_PORT'])) {
        $port = (int)$server['SERVER_PORT'];
    }
    
    // Validate port range
    if ($port !== null && ($port < 1 || $port > 65535)) {
        throw new \InvalidArgumentException("Invalid port number");
    }
    
    $path = parse_url($server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $query = parse_url($server['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '';
    
    return new self($scheme, $host, $path, $query, $port);
}

  public function __construct(
    string $scheme = '',
    string $host = '',
    string $path = '/',
    string $query = '',
    ?int $port = null, // Explicitly nullable int
    string $fragment = '',
    string $userInfo = ''
  ) {
    $this->scheme = strtolower($scheme);
    $this->host = strtolower($host);
    $this->port = $port !== null ? (int)$port : null; // Double type safety
    $this->path = $path;
    $this->query = $query;
    $this->fragment = $fragment;
    $this->userInfo = $userInfo;
  }

  public function getScheme(): string
  {
    return $this->scheme;
  }

  public function getAuthority(): string
  {
    if ($this->host === '') {
      return '';
    }

    $authority = $this->host;
    if ($this->userInfo !== '') {
      $authority = $this->userInfo . '@' . $authority;
    }
    if ($this->port !== null) {
      $authority .= ':' . $this->port;
    }

    return $authority;
  }

  public function getUserInfo(): string
  {
    return $this->userInfo;
  }

  public function getHost(): string
  {
    return $this->host;
  }

  public function getPort(): ?int
  {
    return $this->port !== null && !$this->hasStandardPort() ? $this->port : null;
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function getQuery(): string
  {
    return $this->query;
  }

  public function getFragment(): string
  {
    return $this->fragment;
  }

  public function withScheme($scheme): UriInterface
  {
    $scheme = strtolower($scheme);
    if ($scheme === $this->scheme) {
      return $this;
    }

    $new = clone $this;
    $new->scheme = $scheme;
    return $new;
  }

  public function withUserInfo($user, $password = null): UriInterface
  {
    $info = $user;
    if ($password !== null && $password !== '') {
      $info .= ':' . $password;
    }

    if ($info === $this->userInfo) {
      return $this;
    }

    $new = clone $this;
    $new->userInfo = $info;
    return $new;
  }

  public function withHost($host): UriInterface
  {
    $host = strtolower($host);
    if ($host === $this->host) {
      return $this;
    }

    $new = clone $this;
    $new->host = $host;
    return $new;
  }

  public function withPort($port): UriInterface
  {
    if ($port === $this->port) {
      return $this;
    }

    $new = clone $this;
    $new->port = $port !== null ? (int)$port : null;
    return $new;
  }

  public function withPath($path): UriInterface
  {
    if ($path === $this->path) {
      return $this;
    }

    $new = clone $this;
    $new->path = $path;
    return $new;
  }

  public function withQuery($query): UriInterface
  {
    if ($query === $this->query) {
      return $this;
    }

    $new = clone $this;
    $new->query = $query;
    return $new;
  }

  public function withFragment($fragment): UriInterface
  {
    if ($fragment === $this->fragment) {
      return $this;
    }

    $new = clone $this;
    $new->fragment = $fragment;
    return $new;
  }

  public function __toString(): string
  {
    $uri = '';
    if ($this->scheme !== '') {
      $uri .= $this->scheme . ':';
    }

    if ($this->getAuthority() !== '') {
      $uri .= '//' . $this->getAuthority();
    }

    $uri .= $this->path;
    if ($this->query !== '') {
      $uri .= '?' . $this->query;
    }
    if ($this->fragment !== '') {
      $uri .= '#' . $this->fragment;
    }

    return $uri;
  }

  private function hasStandardPort(): bool
  {
    return ($this->scheme === 'http' && $this->port === 80) ||
    ($this->scheme === 'https' && $this->port === 443);
  }
}