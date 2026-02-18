<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Unit\CacheClearer;

use Andante\TimestampableBundle\CacheClearer\TimestampableCacheClearer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class TimestampableCacheClearerTest extends TestCase
{
    public function testClearRemovesMetadataFileWhenItExists(): void
    {
        $cacheDir = \sys_get_temp_dir().'/timestampable_clearer_test_'.\uniqid('', true);
        $metadataFile = $cacheDir.'/'.TimestampableCacheClearer::METADATA_CACHE_FILENAME;
        (new Filesystem())->mkdir($cacheDir);
        (new Filesystem())->touch($metadataFile);
        self::assertFileExists($metadataFile);

        $clearer = new TimestampableCacheClearer(new Filesystem());
        $clearer->clear($cacheDir);

        self::assertFileDoesNotExist($metadataFile);
        (new Filesystem())->remove($cacheDir);
    }

    public function testClearDoesNothingWhenFileDoesNotExist(): void
    {
        $cacheDir = \sys_get_temp_dir().'/timestampable_clearer_test_'.\uniqid('', true);
        (new Filesystem())->mkdir($cacheDir);
        $metadataFile = $cacheDir.'/'.TimestampableCacheClearer::METADATA_CACHE_FILENAME;
        self::assertFileDoesNotExist($metadataFile);

        $clearer = new TimestampableCacheClearer(new Filesystem());
        $clearer->clear($cacheDir);

        self::assertFileDoesNotExist($metadataFile);
        (new Filesystem())->remove($cacheDir);
    }
}
