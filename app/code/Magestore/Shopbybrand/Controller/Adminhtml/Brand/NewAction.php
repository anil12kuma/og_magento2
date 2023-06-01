<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

class NewAction extends \Magestore\Shopbybrand\Controller\Adminhtml\Brand
{
    /**
     * New action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
