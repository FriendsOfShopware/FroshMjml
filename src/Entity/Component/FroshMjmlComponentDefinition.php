<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\Component;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FroshMjmlComponentDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'frosh_mjml_component';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FroshMjmlComponentEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FroshMjmlComponentCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new StringField('technical_name', 'technicalName', 255))->addFlags(new Required(), new ApiAware()),
            (new StringField('type', 'type', 32))->addFlags(new Required(), new ApiAware()),
            (new TranslatedField('mjmlContent'))->addFlags(new ApiAware()),
            (new TranslatedField('label'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(FroshMjmlComponentTranslationDefinition::class, 'frosh_mjml_component_id'))->addFlags(new Required()),
        ]);
    }
}
