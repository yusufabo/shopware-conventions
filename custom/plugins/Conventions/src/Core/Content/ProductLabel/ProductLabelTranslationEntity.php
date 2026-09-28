<?php

declare(strict_types=1);

namespace Conventions\Core\Content\ProductLabel;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class ProductLabelTranslationEntity extends TranslationEntity
{
    protected string $productLabelId;

    protected ?ProductLabelEntity $productLabel = null;

    protected ?string $name = null;

    public function getProductLabelId(): string
    {
        return $this->productLabelId;
    }

    public function setProductLabelId(string $productLabelId): void
    {
        $this->productLabelId = $productLabelId;
    }

    public function getProductLabel(): ?ProductLabelEntity
    {
        return $this->productLabel;
    }

    public function setProductLabel(ProductLabelEntity $productLabel): void
    {
        $this->productLabel = $productLabel;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
