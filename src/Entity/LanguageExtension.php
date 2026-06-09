<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity;

use Frosh\Mjml\Entity\Component\FroshMjmlComponentTranslationDefinition;
use Frosh\Mjml\Entity\MailTemplate\FroshMjmlMailTemplateTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

class LanguageExtension extends EntityExtension
{
    public function getEntityName(): string
    {
        return LanguageDefinition::ENTITY_NAME;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                FroshMjmlComponentTranslationDefinition::EXTENSION_NAME,
                FroshMjmlComponentTranslationDefinition::class,
                'language_id',
            ))->addFlags(new CascadeDelete()),
        );

        $collection->add(
            (new OneToManyAssociationField(
                FroshMjmlMailTemplateTranslationDefinition::EXTENSION_NAME,
                FroshMjmlMailTemplateTranslationDefinition::class,
                'language_id',
            ))->addFlags(new CascadeDelete()),
        );
    }
}
