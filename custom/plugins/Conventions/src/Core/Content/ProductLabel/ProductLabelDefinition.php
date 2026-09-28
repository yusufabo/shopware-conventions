<?php

declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductLabelDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'product_label';
    // Returns the entity name used by Shopware.

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }
    // Returns the entity class used for Product Labels.

    public function getEntityClass(): string
    {
        return ProductLabelEntity::class;
    }
    // Returns the collection class used for multiple Product Labels.

    public function getCollectionClass(): string
    {
        return ProductLabelCollection::class;
    }
    // Defines the default values for new Product Labels.

    public function getDefaults(): array
    {
        return [
            'priority' => 0,
            'active' => true,
        ];
    }
    // Defines all fields and associations of the Product Label entity.

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            (new TranslatedField('name'))->addFlags(new ApiAware()),
            (new StringField('color', 'color', 7))->addFlags(new ApiAware(), new Required()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new DateTimeField('valid_from', 'validFrom'))->addFlags(new ApiAware()),
            (new DateTimeField('valid_to', 'validTo'))->addFlags(new ApiAware()),

            // Required: a label can't be created without a (named) translation
            (new TranslationsAssociationField(ProductLabelTranslationDefinition::class, 'product_label_id'))
                ->addFlags(new ApiAware(), new Required()),

            new ManyToManyAssociationField(
                'products',
                ProductDefinition::class,
                ProductLabelProductDefinition::class,
                'product_label_id',
                'product_id'
            ),
        ]);
    }
}
