<?php

declare(strict_types=1);

namespace Amasty\ElasticSearchGraphQl\Model\ResourceModel;

use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection as ShopbyCollection;
use Amasty\Xsearch\Model\ResourceModel\StockSorting as XsearchStockSorting;
use Magento\Catalog\Model\ResourceModel\Product\Collection as CatalogCollection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as CatalogSearchCollection;

class StockSorting extends XsearchStockSorting
{
    /**
     * Need add stock sorting on mysql ALWAYS (even elastic enabled)
     * because magento in graphql query PRODUCTS load collection with temp table via mysql
     * @see \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch::getList
     *
     * @param CatalogSearchCollection|ShopbyCollection|CatalogCollection $collection
     * @return bool
     */
    protected function isAllowed($collection): bool
    {
        return !$collection->isLoaded() && $this->getConfig()->isShowOutOfStockLast();
    }
}
