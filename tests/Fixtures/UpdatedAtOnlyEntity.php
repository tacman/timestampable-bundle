<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Fixtures;

use Andante\TimestampableBundle\Timestampable\UpdatedAtTimestampableInterface;

class UpdatedAtOnlyEntity implements UpdatedAtTimestampableInterface
{
    private ?\DateTimeImmutable $updatedAt = null;

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
