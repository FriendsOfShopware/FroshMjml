<?php declare(strict_types=1);

namespace Frosh\Mjml\Twig;

use Frosh\Mjml\Service\MjmlComponentLoader;
use Frosh\Mjml\Twig\Node\MjmlComponentTokenParser;
use Shopware\Core\Framework\Context;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TemplateWrapper;
use Twig\TwigFunction;

class MjmlComponentExtension extends AbstractExtension
{
    public function __construct(
        private readonly MjmlComponentLoader $loader,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'mjml_component',
                $this->renderComponent(...),
                ['needs_environment' => true, 'needs_context' => true, 'is_safe' => ['html']],
            ),
            new TwigFunction(
                'mjml_base',
                $this->loadBase(...),
                ['needs_environment' => true, 'needs_context' => true],
            ),
        ];
    }

    public function getTokenParsers(): array
    {
        return [new MjmlComponentTokenParser()];
    }

    public function renderEmbedded(Environment $environment, array $twigContext, string $name, string $body, array $vars = []): string
    {
        $context = $twigContext['context'] ?? null;
        $salesChannelContext = $context instanceof Context ? $context : Context::createCLIContext();

        $content = $this->loader->loadContent($name, $salesChannelContext);
        if ($content === null) {
            return \sprintf('<!-- mjml component "%s" not found -->', $name);
        }

        $data = array_merge($twigContext, $vars, ['content' => new Markup($body, 'UTF-8')]);

        return $this->unwrapBody($environment->createTemplate($content)->render($data));
    }

    public function loadBase(Environment $environment, array $twigContext, string $name): TemplateWrapper
    {
        $context = $twigContext['context'] ?? null;
        $salesChannelContext = $context instanceof Context ? $context : Context::createCLIContext();

        $content = $this->loader->loadContent($name, $salesChannelContext);

        return $environment->createTemplate($content ?? '<mjml><mj-body></mj-body></mjml>');
    }

    public function renderComponent(Environment $environment, array $twigContext, string $name, array $vars = []): string
    {
        $context = $twigContext['context'] ?? null;
        $salesChannelContext = $context instanceof Context ? $context : Context::createCLIContext();

        $content = $this->loader->loadContent($name, $salesChannelContext);
        if ($content === null) {
            return \sprintf('<!-- mjml component "%s" not found -->', $name);
        }

        $rendered = $environment->createTemplate($content)->render(array_merge($twigContext, $vars));

        return $this->unwrapBody($rendered);
    }

    private function unwrapBody(string $mjml): string
    {
        if (stripos($mjml, '<mjml') === false) {
            return $mjml;
        }

        $inner = preg_replace('#</?(mjml|mj-body)[^>]*>#i', '', $mjml) ?? $mjml;

        return trim($inner);
    }
}
