<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\CancelOrder\Model\CancelOrder::class,
            \Amasty\CancelOrder\Model\ResourceModel\CancelOrder::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
