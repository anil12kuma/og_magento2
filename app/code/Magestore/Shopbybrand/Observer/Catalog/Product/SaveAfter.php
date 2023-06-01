<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Catalog\Product;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class SaveAfter extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getProduct();
        $attributeCode = $this->_brandHelper->getAttributeCode();
        $attributeCode = strtolower($attributeCode);

        $optionId = $product->getData($attributeCode);

        if ($optionId != '') {
            /*
             * update table ms_brand_products by product id
            */
            $newBrand = $this->_brandFactory->create()->load($optionId, 'option_id');
            $newBrand->setProductIds($product->getId());
            $newBrand->save();

        }else{
            // remove product out of brand product
            $oldBrandProduct = $this->_brandProductsFactory->create()->load($product->getId(), 'product_id');
            if ($oldBrandProduct->getId()){
                $oldBrandProduct->delete();
            }
        }
    }
}