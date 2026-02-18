<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Fixtures;

use Andante\TimestampableBundle\Timestampable\CreatedAtTimestampableInterface;

class CreatedAtOnlyEntity implements CreatedAtTimestampableInterface
{
    private ?\DateTimeImmutable $createdAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
