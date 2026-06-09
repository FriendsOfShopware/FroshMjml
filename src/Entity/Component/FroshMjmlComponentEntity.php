<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\Component;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class FroshMjmlComponentEntity extends Entity
{
    use EntityIdTrait;

    final public const TYPE_FRAGMENT = 'fragment';
    final public const TYPE_LAYOUT = 'layout';

    protected string $technicalName;

    protected string $type = self::TYPE_FRAGMENT;

    protected ?string $mjmlContent = null;

    protected ?string $label = null;

    protected ?FroshMjmlComponentTranslationCollection $translations = null;

    public function getTechnicalName(): string
    {
        return $this->technicalName;
    }

    public function setTechnicalName(string $technicalName): void
    {
        $this->technicalName = $technicalName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMjmlContent(): ?string
    {
        return $this->mjmlContent;
    }

    public function setMjmlContent(?string $mjmlContent): void
    {
        $this->mjmlContent = $mjmlContent;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getTranslations(): ?FroshMjmlComponentTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(FroshMjmlComponentTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }
}
