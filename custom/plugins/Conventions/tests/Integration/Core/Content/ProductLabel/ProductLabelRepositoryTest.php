<?php declare(strict_types=1);

namespace Conventions\Tests\Integration\Core\Content\ProductLabel;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductLabelRepositoryTest extends TestCase
{
    // Boots the kernel and wraps every test in a DB transaction that is rolled back
    use IntegrationTestBehaviour;

    private EntityRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = static::getContainer()->get('product_label.repository');
    }

    public function testProductLabelCanBeWrittenAndRead(): void
    {
        $context = Context::createDefaultContext();

        $id = Uuid::randomHex();

        $this->repository->create([
            [
                'id' => $id,
                'name' => 'Test Label',
                'color' => '#FF0000',
                'priority' => 10,
                'active' => true,
            ],
        ], $context);

        $criteria = new Criteria([$id]);

        $label = $this->repository
            ->search($criteria, $context)
            ->first();

        static::assertNotNull($label);
        static::assertSame($id, $label->getId());
        static::assertSame('#FF0000', $label->getColor());
        static::assertSame(10, $label->getPriority());
        static::assertTrue($label->isActive());
    }
}
