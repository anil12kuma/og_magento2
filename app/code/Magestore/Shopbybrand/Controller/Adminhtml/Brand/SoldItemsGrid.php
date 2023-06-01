<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magento\Framework\Controller\ResultFactory;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class SoldItemsGrid extends \Magestore\Shopbybrand\Controller\Adminhtml\Brand
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

        return $resultLayout;
    }
}

