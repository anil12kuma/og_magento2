<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Admin\System\Config;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class ChangeSectionShopbybrand extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attributeCode = $this->_brandHelper->getAttributeCode();
        $oldCode = $this->_objectManager->get('Magento\Backend\Model\Session')->getAttributeCode();
        if($attributeCode != $oldCode){
            $brands = $this->_brandCollectionFactory->create();
            $brandProduct = $this->_brandProductsFactory->create();
            foreach($brands as $brand){
                $brand->deleteUrlRewrite();
                $brandProduct->deleteProductData($brand->getId());
                $brand->delete();
            }
            $this->_brandHelper->updateBrandsFormCatalog();

            //flag for reindex product
            $this->_objectManager->get('\Magestore\Shopbybrand\Model\Indexer\Brand\BrandProductProcessor')->markIndexerAsInvalid();
        }
        $stores = $this->_storeCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('store_id', array('neq' => 0));
        foreach ($stores as $store) {
            $this->_cache->save(serialize(''), 'brand_data_'.$store->getId());
            $this->_cache->save(serialize(''), 'brand_cate_data_'.$store->getId());
        }

    }
}