<?php

declare(strict_types=1);

// Use SQLite in-memory for local/make tests when DATABASE_URL is not set (e.g. not in CI).
if (false === \getenv('DATABASE_URL') || '' === \getenv('DATABASE_URL')) {
    \putenv('DATABASE_URL=sqlite:///:memory:');
    $_ENV['DATABASE_URL'] = 'sqlite:///:memory:';
}
