<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional\CacheWarmer;

use Andante\TimestampableBundle\CacheClearer\TimestampableCacheClearer;
use Andante\TimestampableBundle\CacheWarmer\TimestampableCacheWarmer;
use Andante\TimestampableBundle\Tests\App\CacheWarmerTestAppKernel;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Timestampable\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class CacheWarmerTest extends KernelTestCase
{
    private ?string $cacheDir = null;
    private ?Filesystem $filesystem = null;

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

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getFilesystem(): Filesystem
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }

    protected function tearDown(): void
    {
        if (null !== $this->filesystem && null !== $this->cacheDir) {
            $metadataFile = $this->cacheDir.'/'.TimestampableCacheClearer::METADATA_CACHE_FILENAME;
            if ($this->getFilesystem()->exists($metadataFile)) {
                $this->getFilesystem()->remove($metadataFile);
            }
        }
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    public function testCacheWarmerPopulatesCache(): void
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

        $cacheDir = $kernel->getCacheDir();
        $cacheWarmer = $container->get(TimestampableCacheWarmer::class);
        self::assertInstanceOf(TimestampableCacheWarmer::class, $cacheWarmer);

        $cacheWarmer->warmUp($cacheDir);

        $metadataFile = $cacheDir.'/'.TimestampableCacheClearer::METADATA_CACHE_FILENAME;
        self::assertFileExists($metadataFile);

        $registry = $container->get(Registry::class);
        self::assertInstanceOf(Registry::class, $registry);
        $timestampableMetadata = $registry->getTimestampableMetadata(Address::class);
        self::assertNotNull($timestampableMetadata);
        self::assertNotNull($timestampableMetadata->getCreatedAt());
        self::assertNotNull($timestampableMetadata->getUpdatedAt());
        self::assertSame('created', $timestampableMetadata->getCreatedAt()->getPropertyName());
        self::assertSame('updated', $timestampableMetadata->getUpdatedAt()->getPropertyName());

        $em = $container->get('doctrine.orm.default_entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $metadata = $em->getClassMetadata(Address::class);
        self::assertTrue($metadata->hasField('created'));
        self::assertTrue($metadata->hasField('updated'));

        $kernel->shutdown();
    }

    public function testMetadataCacheWarmerEnabledConfiguration(): void
    {
        $filesystem = $this->getFilesystem();

        $addressEntityConfig = [
            Address::class => [
                'created_at_property_name' => 'created',
                'updated_at_property_name' => 'updated',
            ],
        ];

        $kernel = self::createKernel(['environment' => 'test', 'debug' => true], [
            'andante_timestampable' => [
                'metadata_cache_warmer_enabled' => false,
                'entity' => $addressEntityConfig,
            ],
        ]);
        $this->cacheDir = $kernel->getCacheDir();
        $metadataFile = $this->cacheDir.'/'.TimestampableCacheClearer::METADATA_CACHE_FILENAME;
        $filesystem->remove($metadataFile);

        $kernel->boot();
        self::assertFileDoesNotExist($metadataFile, 'Cache file should not exist when warmer is disabled.');
        $kernel->shutdown();

        $kernel = self::createKernel(['environment' => 'test', 'debug' => true], [
            'andante_timestampable' => [
                'metadata_cache_warmer_enabled' => true,
                'entity' => $addressEntityConfig,
            ],
        ]);
        $this->cacheDir = $kernel->getCacheDir();
        $kernel->boot();
        $container = $kernel->getContainer();
        $metadataFile = $this->cacheDir.'/'.TimestampableCacheClearer::METADATA_CACHE_FILENAME;
        $filesystem->remove($metadataFile);

        $cacheWarmer = $container->get(TimestampableCacheWarmer::class);
        self::assertInstanceOf(TimestampableCacheWarmer::class, $cacheWarmer);
        $cacheWarmer->warmUp($this->cacheDir);

        self::assertFileExists($metadataFile, 'Cache file should exist when warmer is enabled.');
        $kernel->shutdown();
    }
}
