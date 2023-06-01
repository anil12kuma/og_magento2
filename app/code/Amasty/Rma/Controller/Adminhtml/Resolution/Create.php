<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Controller\Adminhtml\Resolution;

use Amasty\Rma\Controller\Adminhtml\AbstractResolution;

class Create extends AbstractResolution
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
