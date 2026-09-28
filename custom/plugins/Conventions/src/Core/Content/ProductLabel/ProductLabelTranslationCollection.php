<?php

declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ProductLabelTranslationEntity>
 */
class ProductLabelTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductLabelTranslationEntity::class;
    }
}
