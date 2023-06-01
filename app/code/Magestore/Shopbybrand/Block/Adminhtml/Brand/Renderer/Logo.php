<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Shopbybrand\Block\Adminhtml\Brand\Renderer;

/**
 * Image renderer.
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Logo extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;
    protected $_brandFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Shopbybrand\Model\BrandFactory $brandFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_brandFactory = $brandFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $storeViewId = $this->getRequest()->getParam('store');
        $brand = $this->_brandFactory->create()->setStoreId($storeViewId)->load($row->getId());
        $srcImage = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . $brand->getLogo();
        if($brand->getLogo()) {
            return '<image width="150" height="50" src ="' . $srcImage . '" alt="' . $brand->getBanner() . '" >';
        }
        return '';
    }
}