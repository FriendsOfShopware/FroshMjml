<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\MailTemplate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FroshMjmlMailTemplateTranslationEntity>
 */
class FroshMjmlMailTemplateTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FroshMjmlMailTemplateTranslationEntity::class;
    }
}
