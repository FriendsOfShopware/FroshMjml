<?php declare(strict_types=1);

namespace Frosh\Mjml;

use Frosh\Mjml\Service\MjmlComponentResolver;
use MjmlPHP\Mjml;
use MjmlPHP\MjmlOptions;
use MjmlPHP\Validation\ValidationLevel;
use Shopware\Core\Framework\Context;

readonly class MjmlCompiler
{
    public function __construct(
        private MjmlComponentResolver $resolver,
    ) {
    }

    public function compile(string $mjml, Context $context): string
    {
        $resolved = $this->resolver->resolve($mjml, $context);

        return Mjml::render($resolved, new MjmlOptions(
            validationLevel: ValidationLevel::Soft,
        ))->html;
    }
}
