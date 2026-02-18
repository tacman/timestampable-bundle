<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Unit\Timestampable\Metadata;

use Andante\TimestampableBundle\Timestampable\Metadata\FieldMapping;
use PHPUnit\Framework\TestCase;

class FieldMappingTest extends TestCase
{
    public function testGetPropertyNameAndColumnName(): void
    {
        $mapping = new FieldMapping('createdAt', 'created_at');
        self::assertSame('createdAt', $mapping->getPropertyName());
        self::assertSame('created_at', $mapping->getColumnName());
    }

    public function testGetColumnNameCanBeNull(): void
    {
        $mapping = new FieldMapping('updatedAt', null);
        self::assertSame('updatedAt', $mapping->getPropertyName());
        self::assertNull($mapping->getColumnName());
    }
}
