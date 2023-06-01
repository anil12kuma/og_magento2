<?php

namespace Gearup\Partner\Model\ResourceModel\Partner;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gearup\Partner\Model\Partner', 'Gearup\Partner\Model\ResourceModel\Partner');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>