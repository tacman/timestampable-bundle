<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional;

use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Organization;

trait TimestampableConfigTrait
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected static function getTimestampableConfig(): array
    {
        return [
            'andante_timestampable' => [
                'default' => [
                    'created_at_property_name' => 'createdAt',
                    'updated_at_property_name' => 'updatedAt',
                ],
                'entity' => [
                    Organization::class => [
                        'created_at_property_name' => 'createdAt',
                    ],
                    Address::class => [
                        'created_at_property_name' => 'created',
                        'updated_at_property_name' => 'updated',
                        'created_at_column_name' => 'created_date',
                        'updated_at_column_name' => 'updated_date',
                    ],
                ],
            ],
        ];
    }
}
