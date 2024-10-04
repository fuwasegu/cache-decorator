<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator;

use Fuwasegu\CacheDecorator\Repositories\InvalidArgumentException;
use Fuwasegu\CacheDecorator\Repositories\Psr16Repository;
use Fuwasegu\CacheDecorator\Repositories\Psr6Repository;
use Fuwasegu\CacheDecorator\Repositories\Repository;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use Throwable;

/**
 * A decorator that caches the result of any instance method
 *
 * @template T of object
 * @mixin T
 */
class CacheDecorator
{
    /**
     * @var int Cache duration (seconds)
     */
    private int $ttl = 60;

    /**
     * @param T $instance The instance to be decorated
     */
    private function __construct(
        private readonly mixed $instance,
        private readonly Repository $cache,
    ) {
    }

    /**
     * @param  T       $instance
     * @return self<T>
     */
    public static function wrap(
        mixed $instance,
        CacheInterface|CacheItemPoolInterface $cache,
    ): self {
        return new self(
            $instance,
            match (true) {
                $cache instanceof CacheInterface => new Psr16Repository($cache),
                $cache instanceof CacheItemPoolInterface => new Psr6Repository($cache),
            },
        );
    }

    /**
     * @return self<T>
     */
    public function ttl(int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @param  array<int, mixed>        $arguments
     * @throws InvalidArgumentException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this->instance, $name)) {
            throw new LogicException("{$name}() does not exist in " . $this->instance::class . '.');
        }

        // Only pure functions are supported
        $this->mustPure($this->instance, $name);

        $key = $this->generateCacheKey($this->instance::class, $name, $arguments);

        // if ttl is 0, then...
        // - do not use Cache
        // - discard existing Cache
        if ($this->ttl === 0) {
            $this->cache->delete($key);

            return $this->instance->{$name}(...$arguments);
        }

        $cache = $this->cache->get($key);

        // If Cache is hit, return it
        if ($cache !== null) {
            return $cache;
        }

        $result = $this->instance->{$name}(...$arguments);

        // Cache result
        $this->cache->set($key, $result, $this->ttl);

        return $result;
    }

    /**
     * @param class-string<T>          $class
     * @param array<int|string, mixed> $args
     */
    private function generateCacheKey(string $class, string $method, array $args): string
    {
        return $class . $method . sha1(serialize($args));
    }

    /**
     * @param T $instance
     */
    private function mustPure(mixed $instance, string $method): void
    {
        try {
            $reflection = new ReflectionClass($instance);
            $reflectionMethod = $reflection->getMethod($method);
            $attributes = $reflectionMethod->getAttributes();

            foreach ($attributes as $attribute) {
                if ($attribute->getName() === Pure::class) {
                    return;
                }
            }
        } catch (Throwable) {
        }

        throw new LogicException($this->instance::class . "::{$method}() is not a pure function, so it is not cacheable.");
    }
}
