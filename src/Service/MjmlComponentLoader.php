<?php declare(strict_types=1);

namespace Frosh\Mjml\Service;

use Frosh\Mjml\Entity\Component\FroshMjmlComponentCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class MjmlComponentLoader
{
    private array $cache = [];

    /**
     * @param EntityRepository<FroshMjmlComponentCollection> $froshMjmlComponentRepository
     */
    public function __construct(
        private readonly EntityRepository $froshMjmlComponentRepository,
    ) {
    }

    public function loadContent(string $technicalName, Context $context): ?string
    {
        $cacheKey = $technicalName . '@' . $context->getLanguageId();
        if (\array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $technicalName));
        $criteria->setLimit(1);

        $component = $this->froshMjmlComponentRepository->search($criteria, $context)->getEntities()->first();

        return $this->cache[$cacheKey] = $component?->getMjmlContent();
    }
}
