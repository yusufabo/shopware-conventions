<?php

declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel\Validation;

use Conventions\Core\Content\ProductLabel\ProductLabelDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Validates product_label writes from every source (admin, Admin API, imports):
 * the color must be a hex color (#RRGGBB) and validFrom must not be after validTo.
 */
class ProductLabelValidator implements EventSubscriberInterface
{
    public const VIOLATION_INVALID_COLOR = 'CONVENTIONS_PRODUCT_LABEL_INVALID_COLOR';
    public const VIOLATION_INVALID_DATE_RANGE = 'CONVENTIONS_PRODUCT_LABEL_INVALID_DATE_RANGE';

    private const COLOR_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'preValidate',
        ];
    }

    public function preValidate(PreWriteValidationEvent $event): void
    {
        foreach ($event->getCommandsForEntity(ProductLabelDefinition::ENTITY_NAME) as $command) {
            if (!$command instanceof InsertCommand && !$command instanceof UpdateCommand) {
                continue;
            }

            // The payload uses storage names (columns), not property names
            $payload = $command->getPayload();
            $violations = new ConstraintViolationList();

            $color = $payload['color'] ?? null;
            if (\is_string($color) && !preg_match(self::COLOR_PATTERN, $color)) {
                $violations->add($this->buildViolation(
                    'The color "{{ value }}" is not a valid hex color, for example #FF0000.',
                    ['{{ value }}' => $color],
                    '/color',
                    $color,
                    self::VIOLATION_INVALID_COLOR
                ));
            }

            // Only checked when both dates are part of this write
            $validFrom = $payload['valid_from'] ?? null;
            $validTo = $payload['valid_to'] ?? null;
            if (\is_string($validFrom) && \is_string($validTo) && $validFrom > $validTo) {
                $violations->add($this->buildViolation(
                    'The end date must be after the start date.',
                    [],
                    '/validTo',
                    $validTo,
                    self::VIOLATION_INVALID_DATE_RANGE
                ));
            }

            if ($violations->count() > 0) {
                $event->getExceptions()->add(new WriteConstraintViolationException($violations, $command->getPath()));
            }
        }
    }

    /**
     * @param array<string, string> $parameters
     */
    private function buildViolation(
        string $messageTemplate,
        array $parameters,
        string $propertyPath,
        string $invalidValue,
        string $code,
    ): ConstraintViolation {
        return new ConstraintViolation(
            str_replace(array_keys($parameters), array_values($parameters), $messageTemplate),
            $messageTemplate,
            $parameters,
            null,
            $propertyPath,
            $invalidValue,
            null,
            $code
        );
    }
}
