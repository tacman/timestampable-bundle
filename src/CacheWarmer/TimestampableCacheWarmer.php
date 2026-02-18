<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\CacheWarmer;

use Andante\TimestampableBundle\Timestampable\Metadata\MetadataFactory;
use Andante\TimestampableBundle\Timestampable\Util\CacheKeyGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\AbstractPhpFileCacheWarmer;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class TimestampableCacheWarmer extends AbstractPhpFileCacheWarmer
{
    private MetadataFactory $metadataFactory;
    private ManagerRegistry $managerRegistry;

    public function __construct(
        MetadataFactory $metadataFactory,
        ManagerRegistry $managerRegistry,
        string $phpArrayFile,
    ) {
        parent::__construct($phpArrayFile);
        $this->metadataFactory = $metadataFactory;
        $this->managerRegistry = $managerRegistry;
    }

    protected function doWarmUp(string $cacheDir, ArrayAdapter $arrayAdapter, ?string $buildDir = null): bool
    {
        /** @var EntityManagerInterface $manager */
        foreach ($this->managerRegistry->getManagers() as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
                $metadata = $this->metadataFactory->create($classMetadata->getName());
                if (null !== $metadata) {
                    $cacheKey = CacheKeyGenerator::generateCacheKey($classMetadata->getName());
                    $item = $arrayAdapter->getItem($cacheKey);
                    $item->set($metadata);
                    $arrayAdapter->save($item);
                }
            }
        }

        return true;
    }

    public function isOptional(): bool
    {
        return true;
    }
}
