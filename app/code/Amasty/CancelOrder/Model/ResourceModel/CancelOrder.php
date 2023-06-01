<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model\ResourceModel;

use Amasty\CancelOrder\Api\Data\CancelOrderInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CancelOrder extends AbstractDb
{
    protected function _construct()
    {
        $this->_setResource('sales');
        $this->_init(CancelOrderInterface::TABLE_NAME, CancelOrderInterface::ID);
    }
}
