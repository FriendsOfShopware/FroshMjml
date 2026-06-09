<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\MailTemplate;

use Shopware\Core\Content\MailTemplate\MailTemplateDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FroshMjmlMailTemplateDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'frosh_mjml_mail_template';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FroshMjmlMailTemplateEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FroshMjmlMailTemplateCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new FkField('mail_template_id', 'mailTemplateId', MailTemplateDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new BoolField('enabled', 'enabled'))->addFlags(new ApiAware()),
            (new TranslatedField('mjmlContent'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(FroshMjmlMailTemplateTranslationDefinition::class, 'frosh_mjml_mail_template_id'))->addFlags(new ApiAware(), new Required()),
            (new OneToOneAssociationField('mailTemplate', 'mail_template_id', 'id', MailTemplateDefinition::class, false))->addFlags(new ApiAware()),
        ]);
    }
}
