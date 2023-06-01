<?php
namespace Gearup\Athelete\Model\ResourceModel;

class Athelete extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('athelete_request', 'id');
    }
}
?>