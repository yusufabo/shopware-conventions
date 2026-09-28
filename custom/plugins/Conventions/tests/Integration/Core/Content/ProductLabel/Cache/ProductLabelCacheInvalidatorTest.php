<?php declare(strict_types=1);

namespace Conventions\Tests\Integration\Core\Content\ProductLabel\Cache;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Events\InvalidateProductCache;
use Shopware\Core\Content\Test\Product\ProductBuilder;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\EventDispatcherBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Test\Stub\Framework\IdsCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductLabelCacheInvalidatorTest extends TestCase
{
    use EventDispatcherBehaviour;
    use IntegrationTestBehaviour;

    private IdsCollection $ids;

    /**
     * @var list<string>
     */
    private array $invalidatedProductIds = [];

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();
        $context = Context::createDefaultContext();

        static::getContainer()->get('product.repository')->create([
            (new ProductBuilder($this->ids, 'label-product'))->price(10)->build(),
        ], $context);

        static::getContainer()->get('product_label.repository')->create([
            [
                'id' => $this->ids->create('label'),
                'name' => 'Sale',
                'color' => '#FF0000',
                'products' => [['id' => $this->ids->get('label-product')]],
            ],
        ], $context);

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $this->addEventListener($dispatcher, InvalidateProductCache::class, function (InvalidateProductCache $event): void {
            array_push($this->invalidatedProductIds, ...$event->getIds());
        });
    }

    public function testUpdatingALabelInvalidatesItsProducts(): void
    {
        static::getContainer()->get('product_label.repository')->update([
            ['id' => $this->ids->get('label'), 'color' => '#00FF00'],
        ], Context::createDefaultContext());

        static::assertContains($this->ids->get('label-product'), $this->invalidatedProductIds);
    }

    public function testDeletingALabelInvalidatesItsProducts(): void
    {
        static::getContainer()->get('product_label.repository')->delete([
            ['id' => $this->ids->get('label')],
        ], Context::createDefaultContext());

        static::assertContains($this->ids->get('label-product'), $this->invalidatedProductIds);
    }
}
