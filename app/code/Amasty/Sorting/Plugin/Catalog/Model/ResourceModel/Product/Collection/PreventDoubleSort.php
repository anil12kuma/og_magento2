<?php

declare(strict_types=1);

namespace Amasty\Sorting\Plugin\Catalog\Model\ResourceModel\Product\Collection;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class PreventDoubleSort
{
    /**
     * Prevent double sorting by some attribute.
     * @see Collection::addAttributeToSort
     */
    public function aroundAddAttributeToSort(
        Collection $collection,
        callable $proceed,
        string $attribute,
        string $dir = Collection::SORT_ORDER_ASC
    ): Collection {
        if (!$collection->getFlag($this->getFlagName($attribute))) {
            $collection->setFlag($this->getFlagName($attribute), true);
            $proceed($attribute, $dir);
        }

        return $collection;
    }

    private function getFlagName(string $attribute): string
    {
        return sprintf('sorted_by_%s_attribute', $attribute);
    }
}
