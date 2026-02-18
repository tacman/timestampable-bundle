<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\App\Clock;

use Psr\Clock\ClockInterface;

/**
 * Decorates a ClockInterface to allow freezing time or setting a specific time
 * for tests. When no time is frozen, delegates to the inner clock.
 */
final class ClockMock implements ClockInterface
{
    private ?\DateTimeImmutable $frozenTime = null;

    public function __construct(
        private readonly ClockInterface $innerClock,
    ) {
    }

    public function now(): \DateTimeImmutable
    {
        if (null !== $this->frozenTime) {
            return $this->frozenTime;
        }

        return $this->innerClock->now();
    }

    /**
     * Set the time returned by now() until unfreeze() is called.
     */
    public function setFrozenTime(\DateTimeImmutable $time): void
    {
        $this->frozenTime = $time;
    }

    /**
     * Freeze time to the current time from the inner clock.
     */
    public function freeze(): void
    {
        $this->frozenTime = $this->innerClock->now();
    }

    /**
     * Resume delegating to the inner clock.
     */
    public function unfreeze(): void
    {
        $this->frozenTime = null;
    }
}
