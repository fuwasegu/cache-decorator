<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator\Repositories;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentExceptionAlias;

class Psr16Repository implements Repository
{
    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    public function get(string $key): mixed
    {
        try {
            return $this->cache->get($key);
        } catch (PsrInvalidArgumentExceptionAlias $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        try {
            return $this->cache->set($key, $value, $ttl);
        } catch (PsrInvalidArgumentExceptionAlias $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function delete(string $key): bool
    {
        try {
            return $this->cache->delete($key);
        } catch (PsrInvalidArgumentExceptionAlias $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
