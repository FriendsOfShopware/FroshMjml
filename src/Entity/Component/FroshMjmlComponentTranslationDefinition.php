<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\Component;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FroshMjmlComponentTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'frosh_mjml_component_translation';

    final public const EXTENSION_NAME = 'froshMjmlComponentTranslations';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FroshMjmlComponentTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FroshMjmlComponentTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return FroshMjmlComponentDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('label', 'label', 255))->addFlags(new ApiAware()),
            (new LongTextField('mjml_content', 'mjmlContent'))->addFlags(new Required(), new ApiAware(), new AllowHtml(false)),
        ]);
    }
}
