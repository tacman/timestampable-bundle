<?php

declare(strict_types=1);

use Andante\TimestampableBundle\Tests\App\Clock\ClockMock;
use Composer\InstalledVersions;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()
        ->set('kernel.secret', 'test_secret')
        ->set('locale', 'en');

    $services = $containerConfigurator->services();

    // Clock mock for tests: decorates real clock and allows freezing time
    $services->set('andante_timestampable.clock.inner', Clock::class);
    $services->set(ClockInterface::class, ClockMock::class)
        ->args([new ReferenceConfigurator('andante_timestampable.clock.inner')]);

    $frameworkBundleVersion = InstalledVersions::getVersion('symfony/framework-bundle');
    $frameworkConfig = [
        'secret' => '%kernel.secret%',
        'test' => true,
    ];
    if (null !== $frameworkBundleVersion && \version_compare($frameworkBundleVersion, '6.1.0', '>=')) {
        $frameworkConfig['http_method_override'] = false;
    }
    if (null !== $frameworkBundleVersion && \version_compare($frameworkBundleVersion, '6.4.0', '>=')) {
        $frameworkConfig['handle_all_throwables'] = true;
        $frameworkConfig['php_errors'] = ['log' => true];
    }
    $containerConfigurator->extension('framework', $frameworkConfig);

    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();

    $doctrineOrm = [
        'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
        'auto_mapping' => true,
        'mappings' => [
            'Fixtures' => [
                'is_bundle' => false,
                'dir' => '%kernel.project_dir%/tests/Fixtures/Entity/',
                'prefix' => 'Andante\TimestampableBundle\Tests\Fixtures\Entity',
                'alias' => 'Fixtures',
            ],
        ],
    ];
    if (null !== $frameworkBundleVersion && \version_compare($frameworkBundleVersion, '6.4.0', '>=')) {
        $doctrineOrm['mappings']['Fixtures']['type'] = 'attribute';
    }

    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'url' => '%env(resolve:DATABASE_URL)%',
        ],
        'orm' => $doctrineOrm,
    ]);
};
