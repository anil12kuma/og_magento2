<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Catalog\Product\Collection;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class BeforeAddCountToCategories extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    public function getlink()
    {
        $link = $this->getRequest()->getRouteName() .
            $this->getRequest()->getControllerName() .
            $this->getRequest()->getActionName() .
            $this->getRequest()->getModuleName();

        return $link;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }
}