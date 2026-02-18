<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Unit\Timestampable\Util;

use Andante\TimestampableBundle\Timestampable\Util\CacheKeyGenerator;
use PHPUnit\Framework\TestCase;

class CacheKeyGeneratorTest extends TestCase
{
    public function testGenerateCacheKeyReplacesBackslashesWithUnderscores(): void
    {
        self::assertSame('Foo_Bar_Baz', CacheKeyGenerator::generateCacheKey('Foo\\Bar\\Baz'));
    }

    public function testGenerateCacheKeyWithSingleSegment(): void
    {
        self::assertSame('Foo', CacheKeyGenerator::generateCacheKey('Foo'));
    }

    public function testGenerateCacheKeyWithFqcn(): void
    {
        $fqcn = 'Andante\\TimestampableBundle\\Tests\\Fixtures\\Entity\\Address';
        self::assertSame(
            'Andante_TimestampableBundle_Tests_Fixtures_Entity_Address',
            CacheKeyGenerator::generateCacheKey($fqcn)
        );
    }
}
