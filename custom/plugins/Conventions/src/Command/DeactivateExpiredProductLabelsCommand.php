<?php declare(strict_types=1);

namespace Conventions\Command;

use Conventions\Core\Content\ProductLabel\Service\ExpiredProductLabelDeactivator;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'conventions:product-label:deactivate-expired',
    description: 'Deactivates product labels whose "valid to" date is in the past',
)]
class DeactivateExpiredProductLabelsCommand extends Command
{
    public function __construct(
        private readonly ExpiredProductLabelDeactivator $deactivator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ids = $this->deactivator->deactivateExpired(Context::createCLIContext());

        (new SymfonyStyle($input, $output))->success(\sprintf('Deactivated %d expired product label(s).', \count($ids)));

        return self::SUCCESS;
    }
}
