<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional\CacheClearer;

use Andante\TimestampableBundle\CacheClearer\TimestampableCacheClearer;
use Andante\TimestampableBundle\CacheWarmer\TimestampableCacheWarmer;
use Andante\TimestampableBundle\Tests\App\CacheWarmerTestAppKernel;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class CacheClearerTest extends KernelTestCase
{
    private ?string $cacheDir = null;

    protected static function getKernelClass(): string
    {
        return CacheWarmerTestAppKernel::class;
    }

    /**
     * @param array<string, mixed>                $options
     * @param array<string, array<string, mixed>> $config
     */
    protected static function createKernel(array $options = [], array $config = []): KernelInterface
    {
        $env = $options['environment'] ?? 'test';
        $debug = (bool) ($options['debug'] ?? true);

        return new CacheWarmerTestAppKernel($env, $debug, $config);
    }

    protected function tearDown(): void
    {
        if (null !== $this->cacheDir) {
            $metadataFile = $this->cacheDir.'/'.TimestampableCacheClearer::METADATA_CACHE_FILENAME;
            if (\file_exists($metadataFile)) {
                \unlink($metadataFile);
            }
        }
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    public function testClearRemovesMetadataFileAfterWarmup(): void
    {
        $kernel = self::createKernel([], [
            'andante_timestampable' => [
                'metadata_cache_warmer_enabled' => true,
                'entity' => [
                    Address::class => [
                        'created_at_property_name' => 'created',
                        'updated_at_property_name' => 'updated',
                    ],
                ],
            ],
        ]);
        $this->cacheDir = $kernel->getCacheDir();
        $kernel->boot();
        $container = $kernel->getContainer();

        $cacheWarmer = $container->get(TimestampableCacheWarmer::class);
        self::assertInstanceOf(TimestampableCacheWarmer::class, $cacheWarmer);
        $cacheWarmer->warmUp($this->cacheDir);

        $metadataFile = $this->cacheDir.'/'.TimestampableCacheClearer::METADATA_CACHE_FILENAME;
        self::assertFileExists($metadataFile, 'Cache should be warmed before clear.');

        $clearer = $container->get(TimestampableCacheClearer::class);
        self::assertInstanceOf(TimestampableCacheClearer::class, $clearer);
        $clearer->clear($this->cacheDir);

        self::assertFileDoesNotExist($metadataFile, 'Clearer should remove the metadata file.');
        $kernel->shutdown();
    }
}
