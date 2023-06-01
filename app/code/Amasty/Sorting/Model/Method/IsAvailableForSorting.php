<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Method;

use Magento\Catalog\Model\Config as CatalogConfig;

/**
 * Check if passed code is amasty method or attribute used for sorting.
 */
class IsAvailableForSorting
{
    /**
     * @var CatalogConfig
     */
    private $catalogConfig;
    
    public function __construct(CatalogConfig $catalogConfig)
    {
        $this->catalogConfig = $catalogConfig;
    }

    public function execute(string $sortingCode): bool
    {
        return array_key_exists($sortingCode, $this->catalogConfig->getAttributesUsedForSortBy());
    }
}
