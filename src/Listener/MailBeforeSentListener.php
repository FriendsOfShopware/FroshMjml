<?php declare(strict_types=1);

namespace Frosh\Mjml\Listener;

use Frosh\Mjml\MjmlCompiler;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeSentEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class MailBeforeSentListener
{
    public function __construct(
        private MjmlCompiler $compiler,
        private LoggerInterface $logger,
    ) {
    }

    #[AsEventListener]
    public function onMailBeforeSent(MailBeforeSentEvent $event): void
    {
        $html = $event->getMessage()->getHtmlBody();

        if (!\is_string($html) || !str_contains($html, '<mj-')) {
            return;
        }

        try {
            $compiled = $this->compiler->compile($html, $event->getContext());
            $event->getMessage()->html($compiled);
        } catch (\Throwable $e) {
            $this->logger->error('MJML compilation failed; sending the original mail body.', [
                'exception' => $e,
            ]);
        }
    }
}
