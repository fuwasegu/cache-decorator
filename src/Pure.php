<?php

declare(strict_types=1);

namespace Fuwasegu\CacheDecorator;

use Attribute;

/**
 * Attribute as a marker for pure functions (methods).
 * Only methods without side effects can be cached by CacheDecorator.
 *
 * Note that this Attribute does not verify the absence of side effects at runtime,
 * so that determination is left to the implementer.
 * In other words, this Attribute should not be added just to cache results with CacheDecorator,
 * but rather, methods with this Attribute (indicating no side effects) can be cached by CacheDecorator.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Pure
{
}
