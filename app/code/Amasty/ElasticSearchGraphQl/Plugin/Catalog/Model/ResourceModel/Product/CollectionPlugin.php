<?php

declare(strict_types=1);

namespace Amasty\ElasticSearchGraphQl\Plugin\Catalog\Model\ResourceModel\Product;

use Amasty\ElasticSearchGraphQl\Model\ResourceModel\StockSorting;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class CollectionPlugin
{
    /**
     * @var bool
     */
    private $isSearchFlag = false;

    /**
     * @var StockSorting
     */
    private $sorting;

    public function __construct(StockSorting $sorting)
    {
        $this->sorting = $sorting;
    }

    public function setSearchFlag(bool $flag = false): void
    {
        $this->isSearchFlag = $flag;
    }

    public function getSearchFlag(): bool
    {
        return $this->isSearchFlag;
    }

    public function beforeLoad(Collection $subject, $printQuery = false, $logQuery = false): array
    {
        if ($this->isSearchFlag) {
            $this->sorting->addOutOfStockSortingToCollection($subject);
        }

        return [$printQuery, $logQuery];
    }
}
