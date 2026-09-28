<?php

declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ProductLabelEntity>
 */
class ProductLabelCollection extends EntityCollection
{
    // Defines which entity this collection contains.

    protected function getExpectedClass(): string
    {
        return ProductLabelEntity::class;
    }
}
