<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator;

use LogicException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use Throwable;

/**
 * A decorator that caches the result of any instance method
 *
 * @template T
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
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @param  T       $instance
     * @return self<T>
     */
    public static function wrap(mixed $instance, CacheInterface $cache): self
    {
        return new self($instance, $cache);
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
     * @param array<int, mixed> $arguments
     * @throws InvalidArgumentException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this->instance, $name)) {
            throw new LogicException("{$name}() does not exist in " . $this->instance::class . ".");
        }

        // 純粋関数のみサポートする
        $this->mustPure($this->instance, $name);

        $key = $this->generateCacheKey($this->instance::class, $name, $arguments);

        $cache = $this->cache->get($key);

        // Cache がヒットしたらそれを返す
        if ($cache !== null) {
            return $cache;
        }

        $result = $this->instance->{$name}(...$arguments);

        // 結果を Cache する
        $this->cache->set($key, $result, $this->ttl);

        return $result;
    }

    /**
     * @param class-string<T>          $class
     * @param array<int|string, mixed> $args
     */
    private function generateCacheKey(string $class, string $method, array $args): string
    {
        if (false === $json = json_encode($args)) {
            throw new LogicException('The arguments must be able to be encoded as JSON.');
        }

        return $class . $method . md5($json);
    }

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
        } catch (Throwable $e) {
        }

        throw new LogicException($this->instance::class . "::{$method}() is not a pure function, so it is not cacheable.");
    }
}
