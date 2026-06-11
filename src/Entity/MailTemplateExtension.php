<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity;

use Frosh\Mjml\Entity\MailTemplate\FroshMjmlMailTemplateDefinition;
use Shopware\Core\Content\MailTemplate\MailTemplateDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class MailTemplateExtension extends EntityExtension
{
    public function getEntityName(): string
    {
        return MailTemplateDefinition::ENTITY_NAME;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField('froshMjml', 'id', 'mail_template_id', FroshMjmlMailTemplateDefinition::class, true))
                ->addFlags(new ApiAware())
        );
    }

    public function getDefinitionClass(): string
    {
        return MailTemplateDefinition::class;
    }
}
