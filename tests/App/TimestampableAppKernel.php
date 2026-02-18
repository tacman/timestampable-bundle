<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\App;

use Andante\TimestampableBundle\Tests\App\Compiler\MakeCacheWarmerServicesPublicCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TimestampableAppKernel extends AppKernel
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new MakeCacheWarmerServicesPublicCompilerPass());
    }
}
