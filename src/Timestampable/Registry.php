<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable;

use Andante\TimestampableBundle\Timestampable\Metadata\Metadata;
use Andante\TimestampableBundle\Timestampable\Metadata\MetadataFactory;
use Andante\TimestampableBundle\Timestampable\Util\CacheKeyGenerator;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;

class Registry
{
    /** @var array<class-string, Metadata|null> */
    private array $loadedMetadata = [];

    public function __construct(
        private MetadataFactory $metadataFactory,
        private PhpArrayAdapter $phpArrayAdapter,
    ) {
    }

    /**
     * @param class-string $entityClass
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getTimestampableMetadata(string $entityClass): ?Metadata
    {
        if (\array_key_exists($entityClass, $this->loadedMetadata)) {
            return $this->loadedMetadata[$entityClass];
        }

        $cacheKey = CacheKeyGenerator::generateCacheKey($entityClass);
        if ($this->phpArrayAdapter->hasItem($cacheKey)) {
            $cachedMetadata = $this->phpArrayAdapter->getItem($cacheKey)->get();
            $this->loadedMetadata[$entityClass] = $cachedMetadata instanceof Metadata ? $cachedMetadata : null;

            return $this->loadedMetadata[$entityClass];
        }

        $this->loadedMetadata[$entityClass] = $this->metadataFactory->create($entityClass);

        return $this->loadedMetadata[$entityClass];
    }
}
