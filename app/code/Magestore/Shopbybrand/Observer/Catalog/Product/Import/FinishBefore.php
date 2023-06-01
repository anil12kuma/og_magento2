<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Catalog\Product\Import;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class FinishBefore extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //flag for reindex product
        $this->_objectManager->get('\Magestore\Shopbybrand\Model\Indexer\Brand\BrandProductProcessor')->markIndexerAsInvalid();

//        $modelBrand = $this->_brandFactory->create()->getCollection();
//        foreach ($modelBrand->getItems() as $value) {
//            $productIds = $this->_brandHelper->getProductIdsByBrand($value);
//            if (is_string($productIds)) {
//                $value->setProductIds($productIds);
//                $value->save();
//            }
//
//            $categoryIds = $this->_brandHelper->getCategoryIdsByBrand($value);
//            if (is_string($categoryIds)) {
//                $value->setCategoryIds($categoryIds);
//                $value->save();
//            }
//        }
        
        $stores = $this->_storeCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('store_id', ['neq' => 0]);
        foreach ($stores as $store) {
            $this->_cache->save(serialize(''), 'brand_data_' . $store->getId());
        }
    }
}