<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator\Repositories;

/**
 * Cache repository only have get, set and delete methods.
 */
interface Repository
{
    /**
     * Fetches a value from the cache.
     *
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with a TTL time.
     *
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Delete an item from the cache by its unique key.
     *
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool;
}
