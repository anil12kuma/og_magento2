<?php
namespace Gearup\Athelete\Model;

class Athelete extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gearup\Athelete\Model\ResourceModel\Athelete');
    }
}
?>