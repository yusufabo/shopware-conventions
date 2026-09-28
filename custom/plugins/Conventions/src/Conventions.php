<?php declare(strict_types=1);

namespace Conventions;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class Conventions extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection = $this->container?->get(Connection::class);
        if (!$connection instanceof Connection) {
            return;
        }

        // Child tables first because of the foreign keys
        $connection->executeStatement('DROP TABLE IF EXISTS `product_label_product`');
        $connection->executeStatement('DROP TABLE IF EXISTS `product_label_translation`');
        $connection->executeStatement('DROP TABLE IF EXISTS `product_label`');
    }
}
