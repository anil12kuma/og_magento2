<?php

declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Model\FilterBuilder;

use Amasty\ShopbyGraphQl\Model\FilterBuilderInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config as EavConfig;

class FilterSorting implements FilterBuilderInterface
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    public function build(array &$filters, int $storeId): void
    {
        usort($filters, [$this, 'sortingByPosition']);
    }

    private function sortingByPosition(array $first, array $second): int
    {
        $attributeA = $this->eavConfig->getAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $this->getAttributeCode($first)
        );

        $attributeB = $this->eavConfig->getAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $this->getAttributeCode($second)
        );

        return $attributeA->getPosition() <=> $attributeB->getPosition();
    }

    private function getAttributeCode(array $filter): string
    {
        return str_replace('_bucket', '', $filter['attribute_code']);
    }
}
