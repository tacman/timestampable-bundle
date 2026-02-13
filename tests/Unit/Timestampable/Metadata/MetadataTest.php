<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Unit\Timestampable\Metadata;

use Andante\TimestampableBundle\Timestampable\Metadata\FieldMapping;
use Andante\TimestampableBundle\Timestampable\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    public function testGettersWithBothFieldsSet(): void
    {
        $createdAt = new FieldMapping('createdAt', 'created_at');
        $updatedAt = new FieldMapping('updatedAt', 'updated_at');
        $metadata = new Metadata(\stdClass::class, $createdAt, $updatedAt);

        self::assertSame(\stdClass::class, $metadata->getEntityClass());
        self::assertSame($createdAt, $metadata->getCreatedAt());
        self::assertSame($updatedAt, $metadata->getUpdatedAt());
    }

    public function testGettersWithOnlyCreatedAtSet(): void
    {
        $createdAt = new FieldMapping('createdAt', null);
        $metadata = new Metadata(\stdClass::class, $createdAt, null);

        self::assertSame(\stdClass::class, $metadata->getEntityClass());
        self::assertSame($createdAt, $metadata->getCreatedAt());
        self::assertNull($metadata->getUpdatedAt());
    }

    public function testGettersWithOnlyUpdatedAtSet(): void
    {
        $updatedAt = new FieldMapping('updatedAt', 'updated_at');
        $metadata = new Metadata(\stdClass::class, null, $updatedAt);

        self::assertSame(\stdClass::class, $metadata->getEntityClass());
        self::assertNull($metadata->getCreatedAt());
        self::assertSame($updatedAt, $metadata->getUpdatedAt());
    }

    public function testGettersWithBothNull(): void
    {
        $metadata = new Metadata(\stdClass::class, null, null);

        self::assertSame(\stdClass::class, $metadata->getEntityClass());
        self::assertNull($metadata->getCreatedAt());
        self::assertNull($metadata->getUpdatedAt());
    }
}
