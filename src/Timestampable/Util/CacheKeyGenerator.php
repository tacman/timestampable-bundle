<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable\Util;

class CacheKeyGenerator
{
    public static function generateCacheKey(string $entityClassFqcn): string
    {
        return \str_replace('\\', '_', $entityClassFqcn);
    }
}
