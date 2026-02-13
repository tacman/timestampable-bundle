<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable\Metadata;

class Metadata
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private string $entityClass,
        private ?FieldMapping $createdAt,
        private ?FieldMapping $updatedAt,
    ) {
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getCreatedAt(): ?FieldMapping
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?FieldMapping
    {
        return $this->updatedAt;
    }
}
