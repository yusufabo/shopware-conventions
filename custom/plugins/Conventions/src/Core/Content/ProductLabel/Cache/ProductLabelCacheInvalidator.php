<?php declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel\Cache;

use Conventions\Core\Content\ProductLabel\ProductLabelDefinition;
use Conventions\Core\Content\ProductLabel\ProductLabelProductDefinition;
use Conventions\Core\Content\ProductLabel\ProductLabelTranslationDefinition;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\Events\InvalidateProductCache;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Invalidates the caches (product pages, listings, streams) of every product whose labels
 * change: a label is created/updated/translated/deleted, or assigned/removed.
 * The actual invalidation is done by Shopware core via InvalidateProductCache.
 */
class ProductLabelCacheInvalidator implements EventSubscriberInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityWrittenContainerEvent::class => 'onEntityWritten',
            EntityDeleteEvent::class => 'onBeforeDelete',
        ];
    }

    /**
     * Single-column primary keys (product_label) are strings, composite ones
     * (product_label_product, product_label_translation) are arrays keyed by property name.
     *
     * @param EntityWrittenContainerEvent<string|array<string, string>> $event
     */
    public function onEntityWritten(EntityWrittenContainerEvent $event): void
    {
        $productIds = [];
        foreach ($event->getPrimaryKeys(ProductLabelProductDefinition::ENTITY_NAME) as $primaryKey) {
            if (\is_array($primaryKey) && \is_string($primaryKey['productId'] ?? null)) {
                $productIds[] = $primaryKey['productId'];
            }
        }

        $labelIds = [];
        foreach ($event->getPrimaryKeys(ProductLabelDefinition::ENTITY_NAME) as $primaryKey) {
            if (\is_string($primaryKey)) {
                $labelIds[] = $primaryKey;
            }
        }
        foreach ($event->getPrimaryKeys(ProductLabelTranslationDefinition::ENTITY_NAME) as $primaryKey) {
            if (\is_array($primaryKey) && \is_string($primaryKey['productLabelId'] ?? null)) {
                $labelIds[] = $primaryKey['productLabelId'];
            }
        }

        $this->invalidate([...$productIds, ...$this->fetchProductIds($labelIds)]);
    }

    public function onBeforeDelete(EntityDeleteEvent $event): void
    {
        $labelIds = array_values(array_filter($event->getIds(ProductLabelDefinition::ENTITY_NAME), \is_string(...)));
        if ($labelIds === []) {
            return;
        }

        // The assignments are removed by ON DELETE CASCADE, so the products must be collected before the delete
        $productIds = $this->fetchProductIds($labelIds);

        $event->addSuccess(function () use ($productIds): void {
            $this->invalidate($productIds);
        });
    }

    /**
     * @param list<string> $labelIds
     *
     * @return list<string>
     */
    private function fetchProductIds(array $labelIds): array
    {
        if ($labelIds === []) {
            return [];
        }

        /** @var list<string> $productIds */
        $productIds = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(`product_id`)) FROM `product_label_product` WHERE `product_label_id` IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList(array_values(array_unique($labelIds)))],
            ['ids' => ArrayParameterType::BINARY],
        );

        return $productIds;
    }

    /**
     * @param list<string> $productIds
     */
    private function invalidate(array $productIds): void
    {
        $productIds = array_values(array_unique($productIds));
        if ($productIds === []) {
            return;
        }

        $this->dispatcher->dispatch(new InvalidateProductCache($productIds));
    }
}
