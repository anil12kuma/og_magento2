<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Controller\Adminhtml\Condition;

use Amasty\Rma\Controller\Adminhtml\AbstractCondition;

class Create extends AbstractCondition
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
