<?php

declare(strict_types=1);

namespace Conventions\Core\Content\Product\Extension;

use Conventions\Core\Content\ProductLabel\ProductLabelDefinition;
use Conventions\Core\Content\ProductLabel\ProductLabelProductDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductLabelExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'labels',
                ProductLabelDefinition::class,
                ProductLabelProductDefinition::class,
                'product_id',
                'product_label_id'
            ))->addFlags(new ApiAware())
        );
    }

    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }
}
