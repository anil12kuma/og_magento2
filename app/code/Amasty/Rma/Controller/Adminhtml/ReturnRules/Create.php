<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Controller\Adminhtml\ReturnRules;

use Amasty\Rma\Controller\Adminhtml\AbstractReturnRules;

class Create extends AbstractReturnRules
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
