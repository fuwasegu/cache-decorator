# Cache Decorator

A PHP library for implementing a cache decorator pattern, allowing easy caching of method results.

## Requirements

- PHP 8.1 or higher

## Installation

You can install the package via composer:

```bash
composer require fuwasegu/cache-decorator
```

## Usage

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
