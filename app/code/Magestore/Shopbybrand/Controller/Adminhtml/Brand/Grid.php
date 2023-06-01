<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

class Grid extends \Magestore\Shopbybrand\Controller\Adminhtml\Brand
{
    /**
     * JSON Grid Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $grid = $this->_view->getLayout()->createBlock('Magestore\Shopbybrand\Block\Adminhtml\Brand\Grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }
}
