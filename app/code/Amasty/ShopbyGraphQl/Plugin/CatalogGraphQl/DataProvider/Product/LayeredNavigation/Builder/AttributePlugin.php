<?php

declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Plugin\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder;

use Amasty\ShopbyGraphQl\Model\FilterBuilder;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Attribute;
use Magento\Framework\Api\Search\AggregationInterface;

class AttributePlugin
{
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        FilterBuilder $filterBuilder
    ) {
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param Attribute $subject
     * @param array $result
     * @param AggregationInterface $aggregation
     * @param int $storeId
     * @return array
     */
    public function afterBuild(Attribute $subject, $result, AggregationInterface $aggregation, $storeId): array
    {
        $this->filterBuilder->build($result, (int) $storeId);

        return $result;
    }
}
