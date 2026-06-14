<?php declare(strict_types=1);

namespace Frosh\Mjml\Controller;

use Frosh\Mjml\MjmlCompiler;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeCollection;
use Shopware\Core\Content\MailTemplate\Service\MailDataSimulator;
use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\ApiRouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
class MjmlPreviewController extends AbstractController
{
    /**
     * @param EntityRepository<MailTemplateTypeCollection> $mailTemplateTypeRepository
     */
    public function __construct(
        // @phpstan-ignore-next-line parameter.deprecatedClass
        private readonly StringTemplateRenderer $templateRenderer,
        private readonly MjmlCompiler $compiler,
        private readonly EntityRepository $mailTemplateTypeRepository,
        // MailDataSimulator only exists on Shopware trunk (unreleased). Optional
        // so the plugin still installs on released 6.7 — the preview then renders
        // without example data.
        private readonly ?MailDataSimulator $simulator = null,
    ) {
    }

    #[Route(
        path: '/api/_action/frosh-mjml/preview',
        name: 'api.action.frosh_mjml.preview',
        defaults: [PlatformRequest::ATTRIBUTE_ACL => ['mail_template:read']],
        methods: [Request::METHOD_POST],
    )]
    public function preview(RequestDataBag $post, Context $context): JsonResponse
    {
        $payload = $post->all();
        $content = \is_string($payload['content'] ?? null) ? $payload['content'] : '';
        $requestData = \is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $exampleData = [];
        $typeId = $payload['mailTemplateTypeId'] ?? null;
        if (\is_string($typeId) && $typeId !== '') {
            $exampleData = $this->buildExampleData($typeId, $context);
        }
        $templateData = array_merge($exampleData, $requestData);

        $this->templateRenderer->enableTestMode();
        try {
            $mjml = $this->templateRenderer->render($content, $templateData, $context);
            $html = $this->compiler->compile($mjml, $context);
        } finally {
            $this->templateRenderer->disableTestMode();
        }

        return new JsonResponse(['html' => $html]);
    }

    private function buildExampleData(string $typeId, Context $context): array
    {
        if ($this->simulator === null) {
            return [];
        }

        try {
            $type = $this->mailTemplateTypeRepository->search(new Criteria([$typeId]), $context)->first();
            if ($type === null) {
                return [];
            }

            return $this->simulator->getTemplateData($type->getTechnicalName(), $context);
        } catch (\Throwable) {
            return [];
        }
    }
}
