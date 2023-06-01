<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Catalog\Product\Attribute;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class UpdateBefore extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attributesData = $observer->getAttributesData();
        $productIds = $observer->getProductIds();
        $attributeCode = $this->_brandHelper->getAttributeCode();
        if(count($productIds)){
            if(isset($attributesData[$attributeCode]) && $attributesData[$attributeCode]){
                $brand = $this->_brandFactory->create()->load($attributesData[$attributeCode], 'option_id');
                $this->_brandHelper->updateProductsForBrands($productIds, $brand);
            }
        }
        $stores = $this->_storeCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('store_id', array('neq' => 0));
        foreach ($stores as $store) {
            $this->_cache->save(serialize(''), 'brand_data_'.$store->getId());
        }
    }
}