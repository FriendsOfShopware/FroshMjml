<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\Component;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class FroshMjmlComponentTranslationEntity extends TranslationEntity
{
    protected string $froshMjmlComponentId;

    protected ?FroshMjmlComponentEntity $froshMjmlComponent = null;

    protected ?string $label = null;

    protected ?string $mjmlContent = null;

    public function getFroshMjmlComponentId(): string
    {
        return $this->froshMjmlComponentId;
    }

    public function setFroshMjmlComponentId(string $froshMjmlComponentId): void
    {
        $this->froshMjmlComponentId = $froshMjmlComponentId;
    }

    public function getFroshMjmlComponent(): ?FroshMjmlComponentEntity
    {
        return $this->froshMjmlComponent;
    }

    public function setFroshMjmlComponent(?FroshMjmlComponentEntity $froshMjmlComponent): void
    {
        $this->froshMjmlComponent = $froshMjmlComponent;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getMjmlContent(): ?string
    {
        return $this->mjmlContent;
    }

    public function setMjmlContent(?string $mjmlContent): void
    {
        $this->mjmlContent = $mjmlContent;
    }
}
