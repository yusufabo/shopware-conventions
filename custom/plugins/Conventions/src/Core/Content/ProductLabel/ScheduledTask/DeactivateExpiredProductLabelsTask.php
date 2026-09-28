<?php declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class DeactivateExpiredProductLabelsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'conventions.product_label.deactivate_expired';
    }

    public static function getDefaultInterval(): int
    {
        return self::HOURLY;
    }
}
