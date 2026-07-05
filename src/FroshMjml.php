<?php declare(strict_types=1);

namespace Frosh\Mjml;

use Shopware\Core\Framework\Plugin;

class FroshMjml extends Plugin
{
    public function executeComposerCommands(): bool
    {
        return true;
    }
}
