<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\Component;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FroshMjmlComponentTranslationEntity>
 */
class FroshMjmlComponentTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'frosh_mjml_component_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return FroshMjmlComponentTranslationEntity::class;
    }
}
