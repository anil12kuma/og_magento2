<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Catalog\Category;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class LoadAfter extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($brand = $this->_coreRegistry->registry('current_brand')) {
            $brandName = $brand->getBrandName();

            $category = $observer['category'];

            if ($category->getId() == $this->_storeManager->getStore()->getRootCategoryId()) {
                $category->setIsAnchor(1)->setName($brandName)->setDisplayMode('PRODUCTS');
            }
        }
    }
}