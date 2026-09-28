<?php

declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProductLabelEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $name = null;

    protected string $color;

    protected int $priority = 0;

    protected bool $active = true;

    protected ?\DateTimeInterface $validFrom = null;

    protected ?\DateTimeInterface $validTo = null;

    protected ?ProductLabelTranslationCollection $translations = null;

    protected ?ProductCollection $products = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * True for light background colors, which need dark text to stay readable.
     */
    public function hasLightColor(): bool
    {
        if (!preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $this->color, $matches)) {
            return false;
        }

        // $matches[0] is the full match including "#", only convert the three color groups
        [$red, $green, $blue] = array_map('hexdec', \array_slice($matches, 1));

        // Perceived brightness (ITU-R BT.601), 0-255
        return ($red * 299 + $green * 587 + $blue * 114) / 1000 > 160;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getValidFrom(): ?\DateTimeInterface
    {
        return $this->validFrom;
    }

    public function setValidFrom(?\DateTimeInterface $validFrom): void
    {
        $this->validFrom = $validFrom;
    }

    public function getValidTo(): ?\DateTimeInterface
    {
        return $this->validTo;
    }

    public function setValidTo(?\DateTimeInterface $validTo): void
    {
        $this->validTo = $validTo;
    }

    public function getTranslations(): ?ProductLabelTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(ProductLabelTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }
}
