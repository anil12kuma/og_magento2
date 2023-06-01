<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block;

/**
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class View extends \Magestore\Shopbybrand\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Shopbybrand::view.phtml';

    /**
     * @var \Magestore\Shopbybrand\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magestore\Shopbybrand\Helper\Brand
     */
    protected $_helperBrand;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param \Magestore\Shopbybrand\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magestore\Shopbybrand\Block\Context $context,
        \Magestore\Shopbybrand\Helper\Data $helper,
        \Magestore\Shopbybrand\Helper\Brand $helperBrand,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_helperBrand = $helperBrand;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magestore\Shopbybrand\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }


    /**
     * @return mixed
     */
    public function getBrand()
    {
        if (!$this->hasData('current_brand')) {
            $brandId = $this->getRequest()->getParam('brand_id');

            $storeId = $this->_storeManager->getStore()->getId();

            /** @var \Magestore\Shopbybrand\Model\Brand $brand */
            $brand = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand');
            $brand->setStoreId($storeId)->load($brandId);
            $this->setData('current_brand', $brand);
        }

        return $this->getData('current_brand');
    }


    /**
     * @return bool
     */
    public function getBannerLink()
    {
        $brand = $this->getBrand();
        if ($brand->getBannerUrl()) {
            return $brand->getBannerUrl();
        } else {
            return false;
        }
    }

    /**
     * @return null|string
     */
    public function getBrandBannerUrl()
    {
        $brand = $this->getBrand();
        if ($brand->getBanner()) {
            $url = $this->_helper->getMediaUrlImage($this->getBrand()->getBanner());
            $img = "<img  src='" . $url . "' title='" . $brand->getBanner() . "' border='0' align='left' '/>";

            return $img;
        } else {
            return null;
        }
    }

    /**
     * @return null|string
     */
    public function getBrandLogoUrl()
    {
        $brand = $this->getBrand();
        if ($brand->getLogo()) {
            $url = $this->_helper->getMediaUrlImage($this->getBrand()->getLogo());
            $img = "<img  src='" . $url . "' title='" . $brand->getLogo() . "' border='0' align='left'/>";

            return $img;
        } else {
            return null;
        }
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        return $storeId;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBrandCategories()
    {
        if (!$this->hasData('brand_categories')) {
            $brand = $this->getBrand();
            $catids = $this->_helperBrand->getCategoryIdsByBrand($brand);
            $catids = explode(",", $catids);

            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
            $categoryCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
            $categoryCollection
                ->setStoreId($this->getStoreId())
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', ['in' => $catids]);
            $this->setData('brand_categories', $categoryCollection);
        }

        return $this->getData('brand_categories');
    }

    /**
     * @return mixed
     */
    public function isDisplayBanner()
    {
        return $this->_systemConfig->isDisplayBrandDetailBanner();
    }

    /**
     * @return mixed
     */
    public function displayLogo()
    {
        return $this->_systemConfig->isDisplayBrandLogo();
    }

    /**
     * @return mixed
     */
    public function isDisPlayBrandDetailsByCategory()
    {
        return $this->_systemConfig->isDisPlayBrandDetailsByCategory();
    }

    /**
     * @return bool
     */
    public function canShowBrandByCategories()
    {
        return $this->_systemConfig->isDisPlayBrandDetailsByCategory() && count($this->getBrandCategories());
    }

    /**
     * @return array
     */
    public function getParentCategories()
    {
        $brand = $this->getBrand();
        $catids = $this->_helperBrand->getCategoryIdsByBrand($brand);
        $catids = explode(",", $catids);
        return $this->_helperBrand->getParentCategories($catids);
    }
}