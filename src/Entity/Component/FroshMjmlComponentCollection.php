<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\Component;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class FroshMjmlComponentCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'frosh_mjml_component_collection';
    }

    protected function getExpectedClass(): string
    {
        return FroshMjmlComponentEntity::class;
    }
}
