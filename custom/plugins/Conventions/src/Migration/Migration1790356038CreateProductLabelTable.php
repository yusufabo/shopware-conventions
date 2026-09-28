<?php

declare(strict_types=1);

namespace Conventions\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1790356038CreateProductLabelTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1790356038;
    }

    public function update(Connection $connection): void
    {
        // Main product_label table
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `product_label` (
                `id` BINARY(16) NOT NULL,
                `color` VARCHAR(7) NOT NULL,
                `priority` INT NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `valid_from` DATETIME(3) NULL,
                `valid_to` DATETIME(3) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8mb4
            COLLATE = utf8mb4_unicode_ci;
        ');

        // Translation table
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `product_label_translation` (
                `product_label_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`product_label_id`, `language_id`),

                CONSTRAINT `fk.product_label_translation.product_label_id`
                    FOREIGN KEY (`product_label_id`)
                    REFERENCES `product_label` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,

                CONSTRAINT `fk.product_label_translation.language_id`
                    FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8mb4
            COLLATE = utf8mb4_unicode_ci;
        ');

        // ManyToMany mapping table
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `product_label_product` (
                `product_label_id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `product_version_id` BINARY(16) NOT NULL,

                PRIMARY KEY (
                    `product_label_id`,
                    `product_id`,
                    `product_version_id`
                ),

                CONSTRAINT `fk.product_label_product.product_label_id`
                    FOREIGN KEY (`product_label_id`)
                    REFERENCES `product_label` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,

                CONSTRAINT `fk.product_label_product.product_id`
                    FOREIGN KEY (`product_id`, `product_version_id`)
                    REFERENCES `product` (`id`, `version_id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            )
            ENGINE = InnoDB
            DEFAULT CHARSET = utf8mb4
            COLLATE = utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
