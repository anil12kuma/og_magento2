<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Shopbybrand\Helper;

/**
 * Class Data
 * @package Magestore\Shopbybrand\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->_backendUrl = $backendUrl;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
    }

    /**
     * get media url of image.
     *
     * @param string $imagePath
     *
     * @return string
     */
    public function getMediaUrlImage($imagePath = '')
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $imagePath;
    }

    /**
     * get Slider Banner Url
     * @return string
     */
    public function getBrandProductsUrl()
    {
        return $this->_backendUrl->getUrl('*/*/products', ['_current' => true]);
    }

    /**
     * @return mixed
     */
    public function getFeaturedBrands()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        /** @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection $brandCollection */
        $brandCollection = $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection')
            //@@TODO fix this func
            ->setOrder('sort_order', 'ASC')
            ->addFieldToFilter('is_featured', ['eq' => 1])
            ->addFieldToFilter('is_active', ['eq' => 1]);

        $systemConfigObj = $this->_objectManager->create('\Magestore\Shopbybrand\Model\SystemConfig');
        $onlyBrandHaveProduct = $systemConfigObj->isDisplayBrandHaveProduct();
        if($onlyBrandHaveProduct == 1){
            foreach ($brandCollection as $key => $item){
                $products_count = count($this->getFeaturedProducts($item->getId()));
                if($products_count == 0){
                    $brandCollection->removeItemByKey($key);
                }
            }
        }
        return $brandCollection;
    }

    /**
     * @param string $brandId
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getFeaturedProducts($brandId = '')
    {
        if (!$brandId) {
            $brandId = $this->_getRequest()->getParam("brand_id");
        }
        /** @var \Magestore\Shopbybrand\Model\Brand $brand */
        $brand = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand');
        $producIds = $brand->getFeaturedProductIds($brandId); //->getAllIds();

//        $visibility = $this->_objectManager->create('Magento\Catalog\Model\Product\Visibility')->getVisibleInCatalogIds();
        $_products = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->addAttributeToSelect(['name', 'product_url', 'small_image'])
//            ->setVisibility($visibility)
            ->addAttributeToFilter('entity_id', ['in' => $producIds]);

        return $_products;
    }

    /**
     * @param $brandId
     *
     * @return mixed
     */
    public function getUrlBanner($brandId)
    {
        $brandImagePathUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $brandImagePathUrl . 'brands/' . $brandId;;
    }

    /**
     * @param $brandId
     *
     * @return mixed
     */
    public function getUrlLogo($brandId)
    {
        $brandImagePathUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $brandImagePathUrl . 'brands/thumbnail/' . $brandId;;
    }
}