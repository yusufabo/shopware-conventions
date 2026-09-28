<?php declare(strict_types=1);

namespace Conventions\Tests\Unit\Subscriber;

use Conventions\Subscriber\ProductLabelCriteriaSubscriber;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class ProductLabelCriteriaSubscriberTest extends TestCase
{
    public function testAddsFilteredAndSortedLabelAssociation(): void
    {
        $criteria = new Criteria();

        (new ProductLabelCriteriaSubscriber())->addLabelAssociation($criteria);

        static::assertTrue($criteria->hasAssociation('labels'));

        $labelCriteria = $criteria->getAssociation('labels');
        $filters = $labelCriteria->getFilters();

        static::assertCount(3, $filters);
        static::assertInstanceOf(EqualsFilter::class, $filters[0]);
        static::assertSame('active', $filters[0]->getField());
        static::assertInstanceOf(MultiFilter::class, $filters[1]);
        static::assertInstanceOf(MultiFilter::class, $filters[2]);

        $sorting = $labelCriteria->getSorting();
        static::assertCount(1, $sorting);
        static::assertSame('priority', $sorting[0]->getField());
        static::assertSame(FieldSorting::DESCENDING, $sorting[0]->getDirection());
    }

    public function testSubscribesToAllProductCriteriaEvents(): void
    {
        static::assertCount(7, ProductLabelCriteriaSubscriber::getSubscribedEvents());
    }
}
