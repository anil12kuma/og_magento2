<?php

namespace Gearup\Athelete\Model\ResourceModel\Athelete;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gearup\Athelete\Model\Athelete', 'Gearup\Athelete\Model\ResourceModel\Athelete');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>