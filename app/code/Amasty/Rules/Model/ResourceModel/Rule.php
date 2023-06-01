<?php

namespace Amasty\Rules\Model\ResourceModel;

/**
 * Resource model for Rule object.
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_amrules_rule';

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
