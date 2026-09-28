<?php

declare(strict_types=1);

namespace Conventions\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Translated columns must be nullable, so a language can fall back to the
 * default language. "Required" is still enforced by the DAL.
 */
class Migration1790450000MakeProductLabelTranslationNameNullable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1790450000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            'ALTER TABLE `product_label_translation` MODIFY `name` VARCHAR(255) NULL'
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
