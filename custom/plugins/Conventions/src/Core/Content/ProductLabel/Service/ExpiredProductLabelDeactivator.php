<?php declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel\Service;

use Conventions\Core\Content\ProductLabel\ProductLabelCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

/**
 * Sets active = false for all active labels whose validTo date is in the past.
 * Used by the scheduled task and the console command.
 */
class ExpiredProductLabelDeactivator
{
    /**
     * @param EntityRepository<ProductLabelCollection> $productLabelRepository
     */
    public function __construct(
        private readonly EntityRepository $productLabelRepository,
    ) {
    }

    /**
     * @return list<string> ids of the deactivated labels
     */
    public function deactivateExpired(Context $context, ?\DateTimeInterface $now = null): array
    {
        $now ??= new \DateTimeImmutable();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new RangeFilter('validTo', [
            RangeFilter::LT => $now->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        $ids = array_values(array_filter(
            $this->productLabelRepository->searchIds($criteria, $context)->getIds(),
            \is_string(...),
        ));

        if ($ids === []) {
            return [];
        }

        // Written through the DAL, so the cache invalidation for the affected products runs as well
        $this->productLabelRepository->update(
            array_map(static fn (string $id): array => ['id' => $id, 'active' => false], $ids),
            $context,
        );

        return $ids;
    }
}
