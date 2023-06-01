<?php
namespace Gearup\Partner\Model;

class Partner extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gearup\Partner\Model\ResourceModel\Partner');
    }
}
?>