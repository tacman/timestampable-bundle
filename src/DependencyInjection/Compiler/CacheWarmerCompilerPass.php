<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\DependencyInjection\Compiler;

use Andante\TimestampableBundle\CacheWarmer\TimestampableCacheWarmer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CacheWarmerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(TimestampableCacheWarmer::class)) {
            return;
        }
        if (!$container->hasParameter('andante_timestampable.metadata_cache_warmer_enabled')
            || !$container->getParameter('andante_timestampable.metadata_cache_warmer_enabled')) {
            $container->getDefinition(TimestampableCacheWarmer::class)->clearTag('kernel.cache_warmer');
        }
    }
}
