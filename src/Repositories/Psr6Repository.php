<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator\Repositories;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;

class Psr6Repository implements Repository
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function get(string $key): mixed
    {
        try {
            return $this->cache->getItem($key)->get();
        } catch (PsrInvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        try {
            $item = $this->cache->getItem($key);
            $item->set($value);

            if ($ttl !== null) {
                $item->expiresAfter($ttl);
            }

            return $this->cache->save($item);
        } catch (PsrInvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function delete(string $key): bool
    {
        try {
            return $this->cache->deleteItem($key);
        } catch (PsrInvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
