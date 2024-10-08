<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator\Tests;

use Fuwasegu\CacheDecorator\CacheDecorator;
use Fuwasegu\CacheDecorator\Pure;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class CacheDecoratorTest extends TestCase
{
    private CacheInterface&MockObject $cache;

    /**
     * @var CacheDecorator<Sample>
     */
    private CacheDecorator $decorator;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(CacheInterface::class);

        // Since PHPStan is unable to infer the type correctly,
        // we can assign the result to a variable first and add a type hint for it.
        /** @var CacheDecorator<Sample> $decorator */
        $decorator = CacheDecorator::wrap(new Sample(), $this->cache);

        $this->decorator = $decorator;
    }

    #[Test]
    public function pureMethodIsCached(): void
    {
        $key = $this->generateCacheKey(Sample::class, 'sum', [2, 3]);

        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with($key, 5, 60);

        $result = $this->decorator->sum(2, 3);

        $this->assertSame(5, $result);
    }

    #[Test]
    public function pureMethodUsingCache(): void
    {
        $key = $this->generateCacheKey(Sample::class, 'sum', [2, 3]);

        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(100);
        $this->cache
            ->expects($this->never())
            ->method('set');

        $result = $this->decorator->sum(2, 3);

        // not 5, but 100 because cache is used.
        $this->assertSame(100, $result);
    }

    #[Test]
    public function pureMethodWithArrayArguments(): void
    {
        $key = $this->generateCacheKey(Sample::class, 'merge', [[1, 2], [3, 4]]);

        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with($key, [1, 2, 3, 4], 60);

        $result = $this->decorator->merge([1, 2], [3, 4]);
        $this->assertEquals([1, 2, 3, 4], $result);
    }

    #[Test]
    public function nonPureMethod(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(Sample::class . '::addState() is not a pure function, so it is not cacheable.');

        $this->decorator->addState(5);
    }

    #[Test]
    public function nonExistentMethodThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('nonExistentMethod() does not exist in ' . Sample::class . '.');

        // @phpstan-ignore method.notFound
        $this->decorator->nonExistentMethod();
    }

    #[Test]
    public function ttlSetting(): void
    {
        $key = $this->generateCacheKey(Sample::class, 'sum', [2, 3]);

        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with($key, 5, 120);

        $this->decorator->ttl(120)->sum(2, 3);
    }

    #[Test]
    public function defaultTtlSetting(): void
    {
        CacheDecorator::setDefaultTtl(123);

        $key = $this->generateCacheKey(Sample::class, 'sum', [2, 3]);

        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);
        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with($key, 5, 123);

        /** @var CacheDecorator<Sample> $decorator */
        $decorator = CacheDecorator::wrap(new Sample(), $this->cache);
        $result = $decorator->sum(2, 3);

        $this->assertSame(5, $result);
    }

    #[Test]
    public function zeroTtlBypassesCache(): void
    {
        $key = $this->generateCacheKey(Sample::class, 'sum', [2, 3]);

        $this->cache
            ->expects($this->once())
            ->method('delete')
            ->with($key);

        $this->cache
            ->expects($this->never())
            ->method('get');

        $this->cache
            ->expects($this->never())
            ->method('set');

        $result = $this->decorator->ttl(0)->sum(2, 3);
        $this->assertEquals(5, $result);
    }

    /**
     * @param array<int|string, mixed> $args
     */
    private function generateCacheKey(string $class, string $method, array $args): string
    {
        return $class . $method . sha1(serialize($args));
    }
}

class Sample
{
    private int $state = 0;

    #[Pure]
    public function sum(int $a, int $b): int
    {
        return $a + $b;
    }

    /**
     * @param  int[] $a
     * @param  int[] $b
     * @return int[]
     */
    #[Pure]
    public function merge(array $a, array $b): array
    {
        return [...$a, ...$b];
    }

    public function addState(int $a): void
    {
        $this->state = + $a;
    }

    public function getState(): int
    {
        return $this->state;
    }
}
