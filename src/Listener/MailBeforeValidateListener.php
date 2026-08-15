<?php declare(strict_types=1);

namespace Frosh\Mjml\Listener;

use Frosh\Mjml\Entity\MailTemplate\FroshMjmlMailTemplateEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateCollection;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class MailBeforeValidateListener
{
    /**
     * @param EntityRepository<MailTemplateCollection> $mailTemplateRepository
     * @param EntityRepository<SalesChannelCollection> $salesChannelRepository
     */
    public function __construct(
        private EntityRepository $mailTemplateRepository,
        private EntityRepository $salesChannelRepository,
    ) {
    }

    #[AsEventListener]
    public function onMailBeforeValidate(MailBeforeValidateEvent $event): void
    {
        $data = $event->getData();

        $templateId = $this->resolveTemplateId($data);
        if ($templateId === null) {
            return;
        }

        /** @var MailTemplateEntity|null $mailTemplate */
        $mailTemplate = $this->mailTemplateRepository
            ->search(new Criteria([$templateId]), $event->getContext())
            ->getEntities()
            ->first();

        /** @var FroshMjmlMailTemplateEntity|null $config */
        $config = $mailTemplate?->getExtension('froshMjml');
        if ($config === null || $config->getTranslation('enabled') !== true) {
            return;
        }

        $mjml = $config->getTranslation('mjmlContent');
        if (!\is_string($mjml) || trim($mjml) === '') {
            return;
        }

        $data['contentHtml'] = $mjml;
        $event->setData($data);

        $this->provideSalesChannelWithoutHeaderFooter($event);
    }

    private function provideSalesChannelWithoutHeaderFooter(MailBeforeValidateEvent $event): void
    {
        $templateData = $event->getTemplateData();
        if (($templateData['salesChannel'] ?? null) instanceof SalesChannelEntity) {
            return;
        }

        $salesChannelId = $event->getData()['salesChannelId'] ?? null;
        if (!\is_string($salesChannelId)) {
            return;
        }

        $criteria = new Criteria([$salesChannelId]);
        $criteria->getAssociation('domains')
            ->addFilter(new EqualsFilter('languageId', $event->getContext()->getLanguageId()));

        $salesChannel = $this->salesChannelRepository
            ->search($criteria, $event->getContext())
            ->getEntities()
            ->first();

        if ($salesChannel === null) {
            return;
        }

        $event->addTemplateData('salesChannel', $salesChannel);
    }

    private function resolveTemplateId(array $data): ?string
    {
        $extensions = \is_array($data['extensions'] ?? null) ? $data['extensions'] : [];

        $candidates = [
            $data['templateId'] ?? null,
            $data['mailTemplateId'] ?? null,
            $extensions['templateId'] ?? null,
            $extensions['mailTemplateId'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (\is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}
