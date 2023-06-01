<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Controller\Adminhtml\Reason;

use Amasty\Rma\Controller\Adminhtml\AbstractReason;

class Create extends AbstractReason
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
