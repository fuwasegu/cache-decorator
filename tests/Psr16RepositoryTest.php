<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator\Tests;

use Fuwasegu\CacheDecorator\Repositories\InvalidArgumentException;
use Fuwasegu\CacheDecorator\Repositories\Psr16Repository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

class Psr16RepositoryTest extends TestCase
{
    private CacheInterface&MockObject $cache;
    private Psr16Repository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->repository = new Psr16Repository($this->cache);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    public function get(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with('test_key')
            ->willReturn('cached value');

        $result = $this->repository->get('test_key');
        $this->assertEquals('cached value', $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    public function set(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with('test_key', 'test value', 3600)
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
        $this->cache
            ->expects($this->once())
            ->method('delete')
            ->with('test_key')
            ->willReturn(true);

        $result = $this->repository->delete('test_key');
        $this->assertTrue($result);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function invalidArgumentException_get(): void
    {
        $exception = $this->createMock(PsrInvalidArgumentException::class);

        $this->cache
            ->expects($this->once())
            ->method('get')
            ->willThrowException($exception);
        $this->expectException(InvalidArgumentException::class);
        $this->repository->get('invalid_key');
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function invalidArgumentException_set(): void
    {
        $exception = $this->createMock(PsrInvalidArgumentException::class);

        $this->cache
            ->expects($this->once())
            ->method('set')
            ->willThrowException($exception);
        $this->expectException(InvalidArgumentException::class);
        $this->repository->set('invalid_key', 'invalid_value');
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function invalidArgumentException_delete(): void
    {
        $exception = $this->createMock(PsrInvalidArgumentException::class);

        $this->cache
            ->expects($this->once())
            ->method('delete')
            ->willThrowException($exception);
        $this->expectException(InvalidArgumentException::class);
        $this->repository->delete('invalid_key');
    }
}
