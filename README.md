![Andante Project Logo](https://github.com/andanteproject/timestampable-bundle/blob/main/andanteproject-logo.png?raw=true)
# Timestampable Bundle
#### Symfony Bundle - [Andante Project](https://github.com/andanteproject)
[![Latest Version](https://img.shields.io/github/release/andanteproject/timestampable-bundle.svg)](https://github.com/andanteproject/timestampable-bundle/releases)
![Github actions](https://github.com/andanteproject/timestampable-bundle/actions/workflows/ci.yml/badge.svg?branch=main)
![Framework](https://img.shields.io/badge/Symfony-5.x|6.x|7.x|8.x-informational?Style=flat&logo=symfony)
![Php8](https://img.shields.io/badge/PHP-8.x-informational?style=flat&logo=php)
![PhpStan](https://img.shields.io/badge/PHPStan-Level%208-success?style=flat&logo=php)

A Symfony Bundle to handle entity `createdAt` and `updatedAt` dates with Doctrine. 🕰

## Requirements
Symfony 5.x–8.x and PHP 8.2.

## Install
Via [Composer](https://getcomposer.org/):
```bash
$ composer require andanteproject/timestampable-bundle
```

## Features
- No configuration required to get started; fully customizable;
- `createdAt` and `updatedAt` properties are `?\DateTimeImmutable`;
- Uses [Symfony Clock](https://symfony.com/doc/current/components/clock.html);
- Does not override your `createdAt` and `updatedAt` values when you set them explicitly;
- No annotations or attributes required;
- Works like magic ✨.

## Basic usage
After [install](#install), ensure the bundle is registered in your Symfony bundles list (`config/bundles.php`):
```php
return [
    // ...
    Andante\TimestampableBundle\AndanteTimestampableBundle::class => ['all' => true],
    // ...
];
```
This is done automatically if you use [Symfony Flex](https://flex.symfony.com). Otherwise, register it manually.

Suppose you have an `App\Entity\Article` Doctrine entity and want to track created and updated dates.
Implement `Andante\TimestampableBundle\Timestampable\TimestampableInterface` and use the `Andante\TimestampableBundle\Timestampable\TimestampableTrait` trait.

```php
<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Andante\TimestampableBundle\Timestampable\TimestampableInterface;
use Andante\TimestampableBundle\Timestampable\TimestampableTrait;

#[ORM\Entity]
class Article implements TimestampableInterface // <-- implement this
{
    use TimestampableTrait; // <-- add this

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    private string $title;
    
    public function __construct(string $title)
    {
        $this->title = $title;
    }
    
    // ...
    // Other properties and methods ...
    // ...
}
```
Update your database schema using your usual Doctrine workflow (e.g. `bin/console doctrine:schema:update --force`, or use [migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.0/reference/introduction.html) for a safer approach).

You should see new columns named `created_at` and `updated_at` ([can I change this?](#configuration-completely-optional)), or similar names depending on your [Doctrine naming strategy](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/namingstrategy.html).

#### You're done! 🎉

`TimestampableInterface` and `TimestampableTrait` are shortcuts that combine `CreatedAtTimestampableInterface` + `CreatedAtTimestampableTrait` and `UpdatedAtTimestampableInterface` + `UpdatedAtTimestampableTrait`. To track only **created** or **updated** dates, use the more specific interfaces and traits below.

| To track | Implement | Use trait |
| --- | --- | --- |
| **Created date only** | `Andante\TimestampableBundle\Timestampable\CreatedAtTimestampableInterface` | `Andante\TimestampableBundle\Timestampable\CreatedAtTimestampableTrait` |
| **Updated date only** | `Andante\TimestampableBundle\Timestampable\UpdatedAtTimestampableInterface` | `Andante\TimestampableBundle\Timestampable\UpdatedAtTimestampableTrait` |
| **Both** | `Andante\TimestampableBundle\Timestampable\TimestampableInterface` | `Andante\TimestampableBundle\Timestampable\TimestampableTrait` |

## Usage without the trait

```php
<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Andante\TimestampableBundle\Timestampable\TimestampableInterface;

#[ORM\Entity]
class Article implements TimestampableInterface // <-- implement this
{
    // No trait needed
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    private string $title;
    
    // DO NOT use ORM attributes to map these properties. See the configuration section for details.
    private ?\DateTimeImmutable $createdAt = null; 
    private ?\DateTimeImmutable $updatedAt = null; 
    
    public function __construct(string $title)
    {
        $this->title = $title;
    }
    
    public function setCreatedAt(\DateTimeImmutable $dateTime): void
    {
        $this->createdAt = $dateTime;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function setUpdatedAt(\DateTimeImmutable $dateTime): void
    {
        $this->updatedAt = $dateTime;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
```
This lets you use different property names (e.g. `created` and `updated` instead of `createdAt` and `updatedAt`). You must specify these in the [bundle configuration](#configuration-completely-optional).

## Metadata cache warming

The bundle discovers which entities are timestampable and how their `createdAt` / `updatedAt` properties and columns are configured. That information is **metadata**: it is computed once and then cached in a PHP file under your cache directory so later requests can reuse it without scanning entities again.

By default, metadata is built **on first use**: the first time a timestampable entity is persisted or updated in a request, the bundle builds metadata for all mapped entities, caches it in memory and writes it to disk. Subsequent requests (and the same request) then use the cached metadata. For most applications this is enough and you do not need to change anything.

If you want to avoid any metadata computation on the **first** request (e.g. in production after a deploy, or in CI when running a single command), you can enable **cache warming**. When enabled, a Symfony cache warmer runs during `cache:warmup` (and when the cache is built on the first request in dev). It precomputes timestampable metadata for all known Doctrine entities and writes it to the cache file. After that, the first real request already uses the prebuilt metadata.

**When to enable it**

- **Leave it disabled** (default) if you are fine with the first request (or first command) doing a bit of extra work once per cache build.
- **Enable it** if you care about consistent cold-start performance (e.g. serverless, CI, or strict SLAs on the first request after deploy).

You can turn it on in the [configuration](#configuration-completely-optional) with `metadata_cache_warmer_enabled: true`.

## Configuration (completely optional)
This bundle is built to save you time and follow best practices out of the box.

You do not need an `andante_timestampable.yaml` config file in your application.

If you need to customize it (e.g. for legacy code), you can change most behavior via the bundle configuration:

### Metadata cache warmer

See [Metadata cache warming](#metadata-cache-warming) for a full explanation. Summary:

| Option | Default | Description |
|--------|---------|-------------|
| `metadata_cache_warmer_enabled` | `false` | Set to `true` to precompute timestampable metadata during cache warmup (e.g. `cache:warmup`). Metadata is written to `%kernel.cache_dir%/timestampable_metadata.php`. |

```yaml
andante_timestampable:
  metadata_cache_warmer_enabled: false  # set to true to prewarm metadata at cache build time
  default:
    created_at_property_name: createdAt # default: createdAt
                                        # Default property for createdAt in entities implementing
                                        # CreatedAtTimestampableInterface or TimestampableInterface
    updated_at_property_name: updatedAt # default: updatedAt
                                        # Default property for updatedAt in entities implementing
                                        # UpdatedAtTimestampableInterface or TimestampableInterface

    created_at_column_name: created_at   # default: null
                                         # Database column name for the created date.
                                         # If null, your Doctrine naming strategy is used
    updated_at_column_name: updated_at   # default: null
                                         # Database column name for the updated date.
                                         # If null, your Doctrine naming strategy is used
  entity: # Per-entity overrides
    Andante\TimestampableBundle\Tests\Fixtures\Entity\Organization:
      created_at_property_name: createdAt
    Andante\TimestampableBundle\Tests\Fixtures\Entity\Address:
      created_at_property_name: created
      updated_at_property_name: updated
      created_at_column_name: created_date
      updated_at_column_name: updated_date
```

Built with ❤️ by the [Andante Project](https://github.com/andanteproject) team.
