<?php declare(strict_types=1);

namespace Frosh\Mjml\Service;

use Frosh\Mjml\Entity\Component\FroshMjmlComponentCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

class MjmlComponentResolver
{
    private const MAX_DEPTH = 5;

    /**
     * @param EntityRepository<FroshMjmlComponentCollection> $froshMjmlComponentRepository
     */
    public function __construct(
        private readonly EntityRepository $froshMjmlComponentRepository,
    ) {
    }

    public function resolve(string $mjml, Context $context): string
    {
        return $this->resolveAtDepth($mjml, $context, 0);
    }

    private function resolveAtDepth(string $mjml, Context $context, int $depth): string
    {
        if ($depth >= self::MAX_DEPTH) {
            return $mjml;
        }

        if (!preg_match_all('/<mj-include\s+name="([^"]+)"\s*\/>/', $mjml, $matches)) {
            return $mjml;
        }

        $technicalNames = array_unique($matches[1]);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('technicalName', $technicalNames));

        $components = $this->froshMjmlComponentRepository->search($criteria, $context)->getEntities();

        $contentMap = [];
        foreach ($components as $component) {
            $contentMap[$component->getTechnicalName()] = $component->getMjmlContent() ?? '';
        }

        $resolved = preg_replace_callback(
            '/<mj-include\s+name="([^"]+)"\s*\/>/',
            static fn (array $match): string => $contentMap[$match[1]] ?? '',
            $mjml,
        );

        if ($resolved === null) {
            throw new \RuntimeException('Failed to resolve mj-include components: ' . preg_last_error_msg());
        }

        if (str_contains($resolved, '<mj-include')) {
            return $this->resolveAtDepth($resolved, $context, $depth + 1);
        }

        return $resolved;
    }
}
