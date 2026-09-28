<?php declare(strict_types=1);

/*
 * Root PHPUnit bootstrap for this Shopware project.
 *
 * Plugins in custom/plugins are not Composer dependencies of the project, so
 * vendor/autoload.php doesn't know their classes. Each plugin's
 * tests/TestBootstrap.php uses Shopware's TestBootstrapper, which:
 *  - uses the separate test database (DATABASE_URL + "_test"),
 *  - installs and activates the plugin there (Shopware's plugin loader then
 *    registers the plugin's "autoload" namespace, e.g. Conventions\),
 *  - registers the plugin's "autoload-dev" namespace (e.g. Conventions\Tests\).
 *
 * Add a plugin here when its tests should run with the root phpunit.dist.xml.
 */
$pluginTestBootstraps = [
    __DIR__ . '/../custom/plugins/Conventions/tests/TestBootstrap.php',
];

foreach ($pluginTestBootstraps as $pluginTestBootstrap) {
    require $pluginTestBootstrap;
}
