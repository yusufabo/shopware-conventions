<?php

declare(strict_types=1);

namespace Conventions\Core\Content\Product\Extension;

use Conventions\Core\Content\ProductLabel\ProductLabelTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

/**
 * Reverse side of product_label_translation.language_id, required by the DAL.
 */
class LanguageExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'productLabelTranslations',
                ProductLabelTranslationDefinition::class,
                'language_id'
            )
        );
    }

    public function getEntityName(): string
    {
        return LanguageDefinition::ENTITY_NAME;
    }
}
