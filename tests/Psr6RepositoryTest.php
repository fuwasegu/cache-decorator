<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator\Tests;

use Fuwasegu\CacheDecorator\Repositories\InvalidArgumentException;
use Fuwasegu\CacheDecorator\Repositories\Psr6Repository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;

class Psr6RepositoryTest extends TestCase
{
    private CacheItemPoolInterface&MockObject $cachePool;
    private Psr6Repository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(CacheItemPoolInterface::class);
        $this->repository = new Psr6Repository($this->cachePool);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    #[Test]
    public function get(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('get')->willReturn('cached value');

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_key')
            ->willReturn($cacheItem);

        $result = $this->repository->get('test_key');
        $this->assertSame('cached value', $result);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    #[Test]
    public function set(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects($this->once())->method('set')->with('test value');
        $cacheItem->expects($this->once())->method('expiresAfter')->with(3600);

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_key')
            ->willReturn($cacheItem);

        $this->cachePool
            ->expects($this->once())
            ->method('save')
            ->with($cacheItem)
            ->willReturn(true);

        $result = $this->repository->set('test_key', 'test value', 3600);
        $this->assertTrue($result);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    public function delete(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('deleteItem')
            ->with('test_key')
            ->willReturn(true);

        $result = $this->repository->delete('test_key');
        $this->assertTrue($result);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function invalidArgumentException(): void
    {
        $exception = $this->createMock(PsrInvalidArgumentException::class);

        // get
        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->willThrowException($exception);
        $this->expectException(InvalidArgumentException::class);
        $this->repository->get('invalid_key');

        // set
        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->willThrowException($exception);
        $this->expectException(InvalidArgumentException::class);
        $this->repository->set('invalid_key', 'invalid_value');

        // delete
        $this->cachePool
            ->expects($this->once())
            ->method('deleteItem')
            ->willThrowException($exception);
        $this->expectException(InvalidArgumentException::class);
        $this->repository->delete('invalid_key');
    }
}
