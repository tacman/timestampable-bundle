<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\App\Compiler;

use Andante\TimestampableBundle\CacheClearer\TimestampableCacheClearer;
use Andante\TimestampableBundle\CacheWarmer\TimestampableCacheWarmer;
use Andante\TimestampableBundle\Timestampable\Registry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakeCacheWarmerServicesPublicCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(TimestampableCacheWarmer::class)) {
            $container->getDefinition(TimestampableCacheWarmer::class)->setPublic(true);
        }
        if ($container->hasDefinition(TimestampableCacheClearer::class)) {
            $container->getDefinition(TimestampableCacheClearer::class)->setPublic(true);
        }
        if ($container->hasDefinition(Registry::class)) {
            $container->getDefinition(Registry::class)->setPublic(true);
        }
    }
}
