<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\MailTemplate;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class FroshMjmlMailTemplateTranslationEntity extends TranslationEntity
{
    protected string $froshMjmlMailTemplateId;

    protected ?FroshMjmlMailTemplateEntity $froshMjmlMailTemplate = null;

    protected ?bool $enabled = null;

    protected ?string $mjmlContent = null;

    public function getFroshMjmlMailTemplateId(): string
    {
        return $this->froshMjmlMailTemplateId;
    }

    public function setFroshMjmlMailTemplateId(string $froshMjmlMailTemplateId): void
    {
        $this->froshMjmlMailTemplateId = $froshMjmlMailTemplateId;
    }

    public function getFroshMjmlMailTemplate(): ?FroshMjmlMailTemplateEntity
    {
        return $this->froshMjmlMailTemplate;
    }

    public function setFroshMjmlMailTemplate(?FroshMjmlMailTemplateEntity $froshMjmlMailTemplate): void
    {
        $this->froshMjmlMailTemplate = $froshMjmlMailTemplate;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
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
