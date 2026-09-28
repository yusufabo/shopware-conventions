<?php declare(strict_types=1);

use Conventions\Command\DeactivateExpiredProductLabelsCommand;
use Conventions\Core\Content\Product\Extension\LanguageExtension;
use Conventions\Core\Content\Product\Extension\ProductLabelExtension;
use Conventions\Core\Content\ProductLabel\Cache\ProductLabelCacheInvalidator;
use Conventions\Core\Content\ProductLabel\ProductLabelDefinition;
use Conventions\Core\Content\ProductLabel\ProductLabelProductDefinition;
use Conventions\Core\Content\ProductLabel\ProductLabelTranslationDefinition;
use Conventions\Core\Content\ProductLabel\ScheduledTask\DeactivateExpiredProductLabelsTask;
use Conventions\Core\Content\ProductLabel\ScheduledTask\DeactivateExpiredProductLabelsTaskHandler;
use Conventions\Core\Content\ProductLabel\Service\ExpiredProductLabelDeactivator;
use Conventions\Core\Content\ProductLabel\Validation\ProductLabelValidator;
use Conventions\Subscriber\ProductLabelCriteriaSubscriber;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Entity definitions and extensions
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

    // Subscribers
    $services->set(ProductLabelCriteriaSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set(ProductLabelValidator::class)
        ->tag('kernel.event_subscriber');

    $services->set(ProductLabelCacheInvalidator::class)
        ->args([service(Connection::class), service('event_dispatcher')])
        ->tag('kernel.event_subscriber');

    // Deactivation of expired labels: scheduled task + console command
    $services->set(ExpiredProductLabelDeactivator::class)
        ->args([service('product_label.repository')]);

    $services->set(DeactivateExpiredProductLabelsTask::class)
        ->tag('shopware.scheduled.task');

    $services->set(DeactivateExpiredProductLabelsTaskHandler::class)
        ->args([service('scheduled_task.repository'), service('logger'), service(ExpiredProductLabelDeactivator::class)])
        ->tag('messenger.message_handler');

    $services->set(DeactivateExpiredProductLabelsCommand::class)
        ->args([service(ExpiredProductLabelDeactivator::class)])
        ->tag('console.command');
};
