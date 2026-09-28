<?php

declare(strict_types=1);

use Conventions\Core\Content\Product\Extension\LanguageExtension;
use Conventions\Core\Content\Product\Extension\ProductLabelExtension;
use Conventions\Core\Content\ProductLabel\ProductLabelDefinition;
use Conventions\Core\Content\ProductLabel\ProductLabelProductDefinition;
use Conventions\Core\Content\ProductLabel\ProductLabelTranslationDefinition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Entity definitions
    $services->set(ProductLabelDefinition::class)
        ->tag('shopware.entity.definition', ['entity' => ProductLabelDefinition::ENTITY_NAME]);

    $services->set(ProductLabelTranslationDefinition::class)
        ->tag('shopware.entity.definition', ['entity' => ProductLabelTranslationDefinition::ENTITY_NAME]);

    $services->set(ProductLabelProductDefinition::class)
        ->tag('shopware.entity.definition', ['entity' => ProductLabelProductDefinition::ENTITY_NAME]);

    $services->set(ProductLabelExtension::class)
        ->tag('shopware.entity.extension');

    $services->set(LanguageExtension::class)
        ->tag('shopware.entity.extension');
};
