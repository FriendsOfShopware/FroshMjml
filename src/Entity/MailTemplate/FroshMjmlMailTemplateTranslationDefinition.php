<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\MailTemplate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FroshMjmlMailTemplateTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'frosh_mjml_mail_template_translation';

    final public const EXTENSION_NAME = 'froshMjmlMailTemplateTranslations';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FroshMjmlMailTemplateTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FroshMjmlMailTemplateTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return FroshMjmlMailTemplateDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new LongTextField('mjml_content', 'mjmlContent'))->addFlags(new ApiAware(), new AllowHtml(false)),
        ]);
    }
}
