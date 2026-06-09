<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\MailTemplate;

use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class FroshMjmlMailTemplateEntity extends Entity
{
    use EntityIdTrait;

    protected string $mailTemplateId;

    protected bool $enabled = false;

    protected ?string $mjmlContent = null;

    protected ?MailTemplateEntity $mailTemplate = null;

    protected ?FroshMjmlMailTemplateTranslationCollection $translations = null;

    public function getMailTemplateId(): string
    {
        return $this->mailTemplateId;
    }

    public function setMailTemplateId(string $mailTemplateId): void
    {
        $this->mailTemplateId = $mailTemplateId;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
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

    public function getMailTemplate(): ?MailTemplateEntity
    {
        return $this->mailTemplate;
    }

    public function setMailTemplate(?MailTemplateEntity $mailTemplate): void
    {
        $this->mailTemplate = $mailTemplate;
    }

    public function getTranslations(): ?FroshMjmlMailTemplateTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(FroshMjmlMailTemplateTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }
}
