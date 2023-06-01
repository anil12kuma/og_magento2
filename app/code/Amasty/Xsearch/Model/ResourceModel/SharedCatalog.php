<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Xsearch
*/


namespace Amasty\Xsearch\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SharedCatalog extends AbstractDb
{
    public const TABLE = 'shared_catalog_product_item';

    public const ID = 'entity_id';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, self::ID);
    }

    /**
     * @param int $customerGroupId
     * @return array
     */
    public function getCatalogItems(int $customerGroupId = 0)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['product_entity' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->joinRight(
            ['shared_product' => $this->getTable(self::TABLE)],
            'shared_product.sku = product_entity.sku',
            []
        )->where(
            'shared_product.customer_group_id = ?',
            $customerGroupId
        );

        return $connection->fetchCol($select);
    }
}
