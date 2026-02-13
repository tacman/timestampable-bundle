<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Unit\Timestampable;

use Andante\TimestampableBundle\Config\Configuration;
use Andante\TimestampableBundle\Tests\Fixtures\CreatedAtOnlyEntity;
use Andante\TimestampableBundle\Tests\Fixtures\UpdatedAtOnlyEntity;
use Andante\TimestampableBundle\Timestampable\Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;

class MetadataFactoryTest extends TestCase
{
    public function testCreateReturnsNullForClassImplementingNeitherInterface(): void
    {
        $configuration = Configuration::createFromArray([]);
        $factory = new MetadataFactory($configuration);

        self::assertNull($factory->create(\stdClass::class));
    }

    public function testCreateReturnsMetadataWithOnlyCreatedAtForCreatedAtOnlyInterface(): void
    {
        $configuration = Configuration::createFromArray([
            'default' => [
                'created_at_property_name' => 'createdAt',
                'created_at_column_name' => 'created_at',
            ],
        ]);
        $factory = new MetadataFactory($configuration);

        $metadata = $factory->create(CreatedAtOnlyEntity::class);
        self::assertNotNull($metadata);
        self::assertSame(CreatedAtOnlyEntity::class, $metadata->getEntityClass());
        self::assertNotNull($metadata->getCreatedAt());
        self::assertSame('createdAt', $metadata->getCreatedAt()->getPropertyName());
        self::assertSame('created_at', $metadata->getCreatedAt()->getColumnName());
        self::assertNull($metadata->getUpdatedAt());
    }

    public function testCreateReturnsMetadataWithOnlyUpdatedAtForUpdatedAtOnlyInterface(): void
    {
        $configuration = Configuration::createFromArray([
            'default' => [
                'updated_at_property_name' => 'updatedAt',
                'updated_at_column_name' => 'updated_at',
            ],
        ]);
        $factory = new MetadataFactory($configuration);

        $metadata = $factory->create(UpdatedAtOnlyEntity::class);
        self::assertNotNull($metadata);
        self::assertSame(UpdatedAtOnlyEntity::class, $metadata->getEntityClass());
        self::assertNull($metadata->getCreatedAt());
        self::assertNotNull($metadata->getUpdatedAt());
        self::assertSame('updatedAt', $metadata->getUpdatedAt()->getPropertyName());
        self::assertSame('updated_at', $metadata->getUpdatedAt()->getColumnName());
    }

    public function testCreateReturnsMetadataWithBothForTimestampableInterfaceUsingConfig(): void
    {
        $configuration = Configuration::createFromArray([
            'default' => [
                'created_at_property_name' => 'createdAt',
                'updated_at_property_name' => 'updatedAt',
                'created_at_column_name' => 'created_at',
                'updated_at_column_name' => 'updated_at',
            ],
            'entity' => [
                \Andante\TimestampableBundle\Tests\Fixtures\Entity\Address::class => [
                    'created_at_property_name' => 'created',
                    'updated_at_property_name' => 'updated',
                    'created_at_column_name' => 'created_date',
                    'updated_at_column_name' => 'updated_date',
                ],
            ],
        ]);
        $factory = new MetadataFactory($configuration);

        $metadata = $factory->create(\Andante\TimestampableBundle\Tests\Fixtures\Entity\Address::class);
        self::assertNotNull($metadata);
        self::assertNotNull($metadata->getCreatedAt());
        self::assertNotNull($metadata->getUpdatedAt());
        self::assertSame('created', $metadata->getCreatedAt()->getPropertyName());
        self::assertSame('created_date', $metadata->getCreatedAt()->getColumnName());
        self::assertSame('updated', $metadata->getUpdatedAt()->getPropertyName());
        self::assertSame('updated_date', $metadata->getUpdatedAt()->getColumnName());
    }
}
