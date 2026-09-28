<?php

declare(strict_types=1);

namespace Conventions\Subscriber;

use Shopware\Core\Content\Product\Events\ProductCrossSellingIdsCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductCrossSellingStreamCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSliderStaticCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSliderStreamCriteriaEvent;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Storefront\Page\Product\ProductPageCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Loads the active, currently valid labels (highest priority first) wherever
 * the storefront renders a product: detail page, listings, search, sliders and cross-selling.
 */
class ProductLabelCriteriaSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Events are matched by their exact class, so every subclass must be listed
        return [
            ProductPageCriteriaEvent::class => 'onProductPageCriteria',
            ProductListingCriteriaEvent::class => 'onListingCriteria',
            ProductSearchCriteriaEvent::class => 'onListingCriteria',
            ProductCrossSellingIdsCriteriaEvent::class => 'onCrossSellingCriteria',
            ProductCrossSellingStreamCriteriaEvent::class => 'onCrossSellingCriteria',
            ProductSliderStaticCriteriaEvent::class => 'onSliderStaticCriteria',
            ProductSliderStreamCriteriaEvent::class => 'onSliderStreamCriteria',
        ];
    }

    public function onProductPageCriteria(ProductPageCriteriaEvent $event): void
    {
        $this->addLabelAssociation($event->getCriteria());
    }

    public function onListingCriteria(ProductListingCriteriaEvent $event): void
    {
        $this->addLabelAssociation($event->getCriteria());
    }

    public function onCrossSellingCriteria(ProductCrossSellingIdsCriteriaEvent|ProductCrossSellingStreamCriteriaEvent $event): void
    {
        $this->addLabelAssociation($event->getCriteria());
    }

    public function onSliderStaticCriteria(ProductSliderStaticCriteriaEvent $event): void
    {
        $this->addLabelAssociation($event->criteria);
    }

    public function onSliderStreamCriteria(ProductSliderStreamCriteriaEvent $event): void
    {
        $this->addLabelAssociation($event->criteria);
    }

    public function addLabelAssociation(Criteria $criteria): void
    {
        $now = (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $criteria->getAssociation('labels')
            ->addFilter(new EqualsFilter('active', true))
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('validFrom', null),
                new RangeFilter('validFrom', [RangeFilter::LTE => $now]),
            ]))
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('validTo', null),
                new RangeFilter('validTo', [RangeFilter::GTE => $now]),
            ]))
            // Highest priority first
            ->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));
    }
}
