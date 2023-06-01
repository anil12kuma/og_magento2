<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magento\Framework\Controller\ResultFactory;
use Magestore\Shopbybrand\Controller\Adminhtml\Brand;

/**
 * Action Edit
 */
class Importbrand extends Brand
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magestore_Shopbybrand::shopbybrand');
        $resultPage->getConfig()->getTitle()->prepend(__('Import Brands'));

        return $resultPage;
    }
}
