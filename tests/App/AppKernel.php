<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\App;

use Andante\TimestampableBundle\AndanteTimestampableBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

abstract class AppKernel extends Kernel
{
    /** @var array<string, array<string, mixed>> */
    protected array $config = [];

    /**
     * @param array<string, array<string, mixed>> $config
     */
    public function __construct(string $environment, bool $debug, array $config = [])
    {
        parent::__construct($environment, $debug);
        $this->config = $config;
    }

    /**
     * @return iterable<int, \Symfony\Component\HttpKernel\Bundle\Bundle>
     */
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new AndanteTimestampableBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.php');

        if (\count($this->config) > 0) {
            $loader->load(function (ContainerBuilder $container): void {
                foreach ($this->config as $extension => $config) {
                    $container->loadFromExtension($extension, $config);
                }
            });
        }
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }

    public function getCacheDir(): string
    {
        return \dirname(__DIR__, 2).'/var/cache/test/'.\hash('crc32b', (string) \json_encode($this->config)).'/';
    }

    public function getLogDir(): string
    {
        return \dirname(__DIR__, 2).'/var/logs/test/';
    }
}
