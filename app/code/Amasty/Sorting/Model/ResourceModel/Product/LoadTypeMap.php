<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;

class LoadTypeMap
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string[] $skus
     * @return array
     */
    public function execute(array $skus): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName('catalog_product_entity'),
            [ProductInterface::SKU, ProductInterface::TYPE_ID]
        )->where(
            sprintf('%s IN (?)', ProductInterface::SKU),
            $skus
        );

        return $this->resourceConnection->getConnection()->fetchPairs($select);
    }
}
