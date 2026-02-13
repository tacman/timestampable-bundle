<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable\Metadata;

use Andante\TimestampableBundle\Config\Configuration;
use Andante\TimestampableBundle\Timestampable\CreatedAtTimestampableInterface;
use Andante\TimestampableBundle\Timestampable\UpdatedAtTimestampableInterface;

class MetadataFactory
{
    public function __construct(
        private Configuration $configuration,
    ) {
    }

    /**
     * @param class-string $entityClass
     */
    public function create(string $entityClass): ?Metadata
    {
        $hasCreatedAt = \is_a($entityClass, CreatedAtTimestampableInterface::class, true);
        $hasUpdatedAt = \is_a($entityClass, UpdatedAtTimestampableInterface::class, true);

        if (!$hasCreatedAt && !$hasUpdatedAt) {
            return null;
        }

        $createdAt = $hasCreatedAt
            ? new FieldMapping(
                $this->configuration->getCreatedAtPropertyNameForClass($entityClass),
                $this->configuration->getCreatedAtColumnNameForClass($entityClass),
            )
            : null;

        $updatedAt = $hasUpdatedAt
            ? new FieldMapping(
                $this->configuration->getUpdatedAtPropertyNameForClass($entityClass),
                $this->configuration->getUpdatedAtColumnNameForClass($entityClass),
            )
            : null;

        return new Metadata(
            entityClass: $entityClass,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
