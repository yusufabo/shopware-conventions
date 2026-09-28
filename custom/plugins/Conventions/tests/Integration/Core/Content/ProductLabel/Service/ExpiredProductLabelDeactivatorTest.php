<?php declare(strict_types=1);

namespace Conventions\Tests\Integration\Core\Content\ProductLabel\Service;

use Conventions\Core\Content\ProductLabel\ProductLabelCollection;
use Conventions\Core\Content\ProductLabel\ProductLabelEntity;
use Conventions\Core\Content\ProductLabel\Service\ExpiredProductLabelDeactivator;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

class ExpiredProductLabelDeactivatorTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testDeactivatesOnlyActiveLabelsWithValidToInThePast(): void
    {
        $context = Context::createDefaultContext();

        /** @var EntityRepository<ProductLabelCollection> $repository */
        $repository = static::getContainer()->get('product_label.repository');

        $expiredId = Uuid::randomHex();
        $validId = Uuid::randomHex();
        $unlimitedId = Uuid::randomHex();

        $repository->create([
            ['id' => $expiredId, 'name' => 'Expired', 'color' => '#FF0000', 'validTo' => '2000-01-01 00:00:00'],
            ['id' => $validId, 'name' => 'Still valid', 'color' => '#00FF00', 'validTo' => '2999-01-01 00:00:00'],
            ['id' => $unlimitedId, 'name' => 'No end date', 'color' => '#0000FF'],
        ], $context);

        $deactivated = static::getContainer()->get(ExpiredProductLabelDeactivator::class)->deactivateExpired($context);

        static::assertSame([$expiredId], $deactivated);

        $labels = $repository->search(new Criteria([$expiredId, $validId, $unlimitedId]), $context)->getEntities();

        static::assertFalse($this->getLabel($labels, $expiredId)->isActive());
        static::assertTrue($this->getLabel($labels, $validId)->isActive());
        static::assertTrue($this->getLabel($labels, $unlimitedId)->isActive());
    }

    private function getLabel(ProductLabelCollection $labels, string $id): ProductLabelEntity
    {
        $label = $labels->get($id);
        static::assertInstanceOf(ProductLabelEntity::class, $label);

        return $label;
    }
}
