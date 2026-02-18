<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable\Metadata;

class FieldMapping
{
    public function __construct(
        private string $propertyName,
        private ?string $columnName,
    ) {
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getColumnName(): ?string
    {
        return $this->columnName;
    }
}
