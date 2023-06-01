<?php
namespace Gearup\Partner\Model\ResourceModel;

class Partner extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('partner_request', 'id');
    }
}
?>