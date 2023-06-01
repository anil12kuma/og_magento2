<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;

class BrandProducts extends AbstractModel
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magestore\Shopbybrand\Helper\Data
     */
    protected $helperBrand;

    /**
     * Model constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magestore\Shopbybrand\Helper\Brand $helperBrand,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->helperBrand = $helperBrand;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Shopbybrand\Model\ResourceModel\BrandProducts');
    }


    public function updateProductDataByBrand($productId, $brandId = null)
    {
        if($brandId && $productId){
            $collection = $this->getCollection()
//                ->addFieldToFilter('brand_id', ['eq' => $brandId])
                ->addFieldToFilter('product_id', ['eq' => $productId]);
            ;

            // case 1: neu product id da ton tai va cap nhat brand moi thi se thay doi brand id
            if($collection->getSize()){
                foreach ($collection as $item) {
                    if($item->getBrandId() != $brandId) {
                        $item->setBrandId($brandId);
                        $item->save();
                    }
                }
            }else{
                // case 2: neu product id chua ton tai thi them moi cac gia tri
                $data['brand_id'] = $brandId;
                $data['product_id'] = $productId;
                $data['is_featured'] = 0;
                $data['position'] = 0;
                $data['visibility_status'] = $this->getVisibilityStatusByProduct($productId);;
                $data['category_ids'] = $this->getCategoriesByProduct($productId);
                $this->setData($data);
                $this->save();
            }
        }
    }


    /**
     * @param $proIds
     * @param $featuredProduct
     */
    public function updateProductData($proIds, $featuredProduct, $brandId = null, $visibility_status = [])
    {
        $positionArray = [];
        parse_str($proIds, $positionArray);
        $dataArray = [];
        $category_id = '';

        foreach ($positionArray as $key => $value) {
            $product = [];
            parse_str(base64_decode($value), $product);
            $dataArray[$key] = $product;
        }

        foreach ($dataArray as $key => $value) {
            $dataArray[$key]['is_featured'] = 0;
            $dataArray[$key]['visibility_status'] = '';
            $dataArray[$key]['category_id'] = $this->getCategoriesByProduct($key);
        }

        foreach ($featuredProduct as $value) {
            if(array_key_exists($value, $dataArray)){
                $dataArray[$value]['is_featured'] = 1;
            }
        }

        foreach ($visibility_status as $key => $value) {
            if(array_key_exists($key, $dataArray)){
                $dataArray[$key]['visibility_status'] = $value;
            }
        }

        $productIds = array_unique(array_keys($dataArray));

        if($brandId){
            $collection = $this->getCollection()
//                ->addFieldToFilter('product_id', ['in' => $productIds])
                ->addFieldToFilter('brand_id', ['eq' => $brandId]);
        }else{
            $collection = $this->getCollection()
                ->addFieldToFilter('product_id', ['in' => $productIds]);
        }

        foreach ($collection as $item) {

            if(in_array($item->getProductId(), $productIds)) {

                if (!isset($dataArray[$item->getProductId()]['position'])) {
                    $dataArray[$item->getProductId()]['position'] = '';
                }

                $posi = (isset($dataArray[$item->getProductId()]['position']) &&
                    is_numeric($dataArray[$item->getProductId()]['position']) &&
                    $dataArray[$item->getProductId()]['position'] >= 0
                ) ? $dataArray[$item->getProductId()]['position'] : 0;

                $item->setBrandId($brandId)
                    ->setPosition($posi);

                //if($dataArray[$item->getProductId()]['is_featured']){
                $item->setIsFeatured($dataArray[$item->getProductId()]['is_featured']);
                //}

                if($dataArray[$item->getProductId()]['visibility_status']){
                    $item->setVisibilityStatus($dataArray[$item->getProductId()]['visibility_status']);
                }

                if($dataArray[$item->getProductId()]['category_id']){
                    $item->setCategoryIds($dataArray[$item->getProductId()]['category_id']);
                }

                $item->save();

                unset($dataArray[$item->getProductId()]);

            } else {
                $product = $this->productFactory->create()->load((int)$item->getProductId());
                if($product->getId()){
                    $attributeCode = $this->helperBrand->getAttributeCode();
                    $product->setData($attributeCode, null)->getResource()->saveAttribute($product, $attributeCode);
                    $product->save();
                }

                $item->delete();
            }
        }

        foreach ($dataArray as $key => $value) {
            $position = isset($value['position']) && (is_numeric($value['position']) && $value['position'] >= 0) ? $value['position'] : 0;

            $this->setBrandId($brandId)
                ->setProductId($key)
                ->setPosition($position);

            //if($value['is_featured']){
            $this->setIsFeatured($value['is_featured']);
            //}

            if($value['visibility_status']){
                $this->setVisibilityStatus($value['visibility_status']);
            }
            if($value['category_id']) {
                $this->setCategoryIds($value['category_id']);
            }
            $this->setId(null);
            $this->save();
        }
    }

    /**
     * @param $brandId
     */
    public function deleteProductData($brandId = null)
    {
        $collection = $this->getCollection()->addFieldToFilter('brand_id', ['eq' => $brandId]);

        foreach ($collection as $item) {
            $item->delete();
        }
    }

    protected function getCategoriesByProduct($product_id)
    {
        $cats = '';
        $product = $this->productFactory->create()->load($product_id);
        if($product->getId()){
            $cats = $product->getCategoryIds();
            if(count($cats) ){
                $cats = implode(",",$cats);
            }else{
                $cats = '';
            }
        }
        return $cats;
    }

    protected function getVisibilityStatusByProduct($product_id)
    {
        $product = $this->productFactory->create()->load($product_id);
        if($product->getId()){
            return $product->getVisibility();
        }
        return '';
    }
}