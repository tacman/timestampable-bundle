![Andante Project Logo](https://github.com/andanteproject/timestampable-bundle/blob/main/andanteproject-logo.png?raw=true)
# Timestampable Bundle 
#### Symfony Bundle - [AndanteProject](https://github.com/andanteproject)
[![Latest Version](https://img.shields.io/github/release/andanteproject/timestampable-bundle.svg)](https://github.com/andanteproject/timestampable-bundle/releases)
![Github actions](https://github.com/andanteproject/timestampable-bundle/actions/workflows/ci.yml/badge.svg?branch=main)
![Framework](https://img.shields.io/badge/Symfony-5.x|6.x|7.x|8.x-informational?Style=flat&logo=symfony)
![Php8](https://img.shields.io/badge/PHP-%208.x-informational?style=flat&logo=php)
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
    // bundles...
    Andante\TimestampableBundle\AndanteTimestampableBundle::class => ['all' => true],
    // bundles...
];
```
This is done automatically if you use [Symfony Flex](https://flex.symfony.com). Otherwise, register it manually.

Suppose you have an `App\Entity\Article` Doctrine entity and want to track created and updated dates.
All you need to do is implement `Andante\TimestampableBundle\Timestampable\TimestampableInterface` and use the `Andante\TimestampableBundle\Timestampable\TimestampableTrait` trait.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Andante\TimestampableBundle\Timestampable\TimestampableInterface;
use Andante\TimestampableBundle\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity()
 */
class Article implements TimestampableInterface // <-- implement this
{
    use TimestampableTrait; // <-- add this

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
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

use Doctrine\ORM\Mapping as ORM;
use Andante\TimestampableBundle\Timestampable\TimestampableInterface;

/**
 * @ORM\Entity()
 */
class Article implements TimestampableInterface // <-- implement this
{
    // No trait needed
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;
    
    // DO NOT use ORM annotations to map these properties. See the configuration section for details.
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

## Configuration (completely optional)
This bundle is built to save you time and follow best practices out of the box.

You do not need an `andante_timestampable.yml` config file in your application.

If you need to customize it (e.g. for legacy code), you can change most behavior via the bundle configuration:
```yaml
andante_timestampable:
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
