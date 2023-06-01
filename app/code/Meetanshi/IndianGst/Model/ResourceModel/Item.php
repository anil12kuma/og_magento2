<?php
/**
 * Provider: Meetanshi.
 * Package: @_LiveData
 * Support: support@meetanshi.com (https://meetanshi.com/)
 */

namespace Meetanshi\IndianGst\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_order_item', 'item_id');
    }
}
