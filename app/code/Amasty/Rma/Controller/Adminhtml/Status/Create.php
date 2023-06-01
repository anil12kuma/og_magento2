<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Controller\Adminhtml\Status;

use Amasty\Rma\Controller\Adminhtml\AbstractStatus;

class Create extends AbstractStatus
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
