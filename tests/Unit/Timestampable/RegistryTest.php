<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Unit\Timestampable;

use Andante\TimestampableBundle\Timestampable\Metadata\Metadata;
use Andante\TimestampableBundle\Timestampable\Metadata\MetadataFactory;
use Andante\TimestampableBundle\Timestampable\Registry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;

class RegistryTest extends TestCase
{
    public function testGetTimestampableMetadataReturnsFactoryResultAndCachesInMemory(): void
    {
        $metadata = new Metadata(\stdClass::class, null, null);
        $factory = $this->createMock(MetadataFactory::class);
        $factory->expects(self::once())
            ->method('create')
            ->with(\stdClass::class)
            ->willReturn($metadata);

        $arrayAdapter = new ArrayAdapter();
        $phpArrayAdapter = new PhpArrayAdapter(
            \sys_get_temp_dir().'/timestampable_registry_test_'.\uniqid('', true).'.php',
            $arrayAdapter
        );
        $registry = new Registry($factory, $phpArrayAdapter);

        $result1 = $registry->getTimestampableMetadata(\stdClass::class);
        $result2 = $registry->getTimestampableMetadata(\stdClass::class);

        self::assertSame($metadata, $result1);
        self::assertSame($metadata, $result2);
    }

    public function testGetTimestampableMetadataReturnsCachedValueFromAdapterWhenPresent(): void
    {
        $cachedMetadata = new Metadata(\stdClass::class, null, null);
        $arrayAdapter = new ArrayAdapter();
        $cacheKey = \str_replace('\\', '_', \stdClass::class);
        $item = $arrayAdapter->getItem($cacheKey);
        $item->set($cachedMetadata);
        $arrayAdapter->save($item);

        $phpArrayAdapter = new PhpArrayAdapter(
            \sys_get_temp_dir().'/timestampable_registry_test_'.\uniqid('', true).'.php',
            $arrayAdapter
        );
        $factory = $this->createMock(MetadataFactory::class);
        $factory->expects(self::never())->method('create');

        $registry = new Registry($factory, $phpArrayAdapter);
        $result = $registry->getTimestampableMetadata(\stdClass::class);

        self::assertNotNull($result);
        self::assertSame(\stdClass::class, $result->getEntityClass());
        self::assertNull($result->getCreatedAt());
        self::assertNull($result->getUpdatedAt());
    }
}
