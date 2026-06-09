<?php declare(strict_types=1);

namespace Frosh\Mjml\Entity\MailTemplate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class FroshMjmlMailTemplateCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FroshMjmlMailTemplateEntity::class;
    }
}
