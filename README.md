# Cache Decorator

[![Coverage Status](https://coveralls.io/repos/github/fuwasegu/cache-decorator/badge.svg?branch=main)](https://coveralls.io/github/fuwasegu/cache-decorator?branch=main)
[![MIT License](http://img.shields.io/badge/license-MIT-blue.svg?style=flat)](LICENSE)

A PHP library for implementing a cache decorator pattern, allowing easy caching of method results.

## Requirements

- PHP 8.1 or higher

## Installation

You can install the package via composer:

```bash
composer require fuwasegu/cache-decorator
```

## Usage

> [!CAUTION]
> Since serialization costs can be high, caching should primarily be applied to I/O bottleneck scenarios, 
> such as HTTP communication and relational database interactions. 
> It is also useful for computationally expensive algorithms (e.g., full search).
> 
> Moreover, the caching driver should utilize lighter options like Redis or Memcached instead of relying on the heavier I/O operations to achieve expected performance improvements.

Here's a basic example of how to use the cache decorator:

```php
use Fuwasegu\CacheDecorator;
use Some\CacheImplementation;

class ExpensiveOperation
{
    #[Pure]
    public function heavyComputation($param)
    {
        // Some expensive operation
        sleep(5);
        return "Result for $param";
    }
}

$cacheImplementation = new CacheImplementation();
$decorator = CacheDecorator::wrap(new ExpensiveOperation(), $cacheImplementation);

// First call will be slow
$result1 = $decorator->heavyComputation('test');

// Second call will be fast, returning cached result
$result2 = $decorator->heavyComputation('test');
```

Only pure methods can be cached.
In order to mark a method as pure, use the `#[Pure]` attribute, not the `#[Pure]` attribute provided by PhpStorm.

> [!NOTE]
> The advantage of this library is that, since CacheDecorator::class internally utilizes Generics and Mixin, 
> developers can wrap any instance without losing the experience, as the methods of the wrapped instance are still auto-completed in the IDE from the CacheDecorator instance.

### Especially in Laravel

In Laravel, you can use `Illuminate\Cache\Repository` as the cache implementation.

```php
class SampleController
{
    public function __construct(
        private Illuminate\Cache\Repository $cache,
    ) {}
    
    public function __index(): Response
    {
        $decorator = CacheDecorator::wrap(new ExpensiveOperation(), $this->cache);
        
        $result = $decorator->heavyComputation('test');
    }
}
```

### Supporting multiple cache implementations

The `CacheDecorator::wrap()` method supports both PSR-16 SimpleCache and PSR-6 Cache interfaces. 
This means you can use either `Psr\SimpleCache\CacheInterface` or `Psr\Cache\CacheItemPoolInterface` implementations as the cache backend.

Example:

```php
// Using PSR-16 SimpleCache
$psr16Cache = new SomePsr16CacheImplementation();
$decorator = CacheDecorator::wrap(new ExpensiveOperation(), $psr16Cache);

// Using PSR-6 Cache
$psr6Cache = new SomePsr6CacheImplementation();
$decorator = CacheDecorator::wrap(new ExpensiveOperation(), $psr6Cache);
```

This flexibility allows you to use the cache implementation that best fits your project's needs.

#### FYI

- [PSR-16 SimpleCache](https://www.php-fig.org/psr/psr-16/)
- [PSR-16 GitHub](https://github.com/php-fig/simple-cache)
- [PSR-6 Cache](https://www.php-fig.org/psr/psr-6/)
- [PSR-6 GitHub](https://github.com/php-fig/cache)
