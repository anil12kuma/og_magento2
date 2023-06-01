<?php
namespace Meetanshi\IndianGst\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

abstract class Item extends AbstractModel implements IdentityInterface
{
    abstract function getIdentities();
    protected function _construct()
    {
        $this->_init('Meetanshi\IndianGst\Model\ResourceModel\Item');
    }
}
