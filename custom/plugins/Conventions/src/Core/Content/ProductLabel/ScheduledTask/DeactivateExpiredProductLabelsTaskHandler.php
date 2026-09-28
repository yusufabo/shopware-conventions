<?php declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel\ScheduledTask;

use Conventions\Core\Content\ProductLabel\Service\ExpiredProductLabelDeactivator;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: DeactivateExpiredProductLabelsTask::class)]
final class DeactivateExpiredProductLabelsTaskHandler extends ScheduledTaskHandler
{
    /**
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $exceptionLogger,
        private readonly ExpiredProductLabelDeactivator $deactivator,
    ) {
        parent::__construct($scheduledTaskRepository, $exceptionLogger);
    }

    public function run(): void
    {
        $this->deactivator->deactivateExpired(Context::createCLIContext());
    }
}
