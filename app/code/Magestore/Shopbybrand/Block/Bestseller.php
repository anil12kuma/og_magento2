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
class Bestseller extends \Magestore\Shopbybrand\Block\AbstractBlock
{
    protected $_template = 'Magestore_Shopbybrand::bestsellersidebar.phtml';

    /**
     * Block constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */

    protected $_brandFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $_saleItemsFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Bestseller constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Shopbybrand\Model\BrandFactory $brandFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $_saleItemsFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        \Magestore\Shopbybrand\Block\Context $context,
        \Magestore\Shopbybrand\Model\BrandFactory $brandFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $_saleItemsFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->_brandFactory = $brandFactory;
        $this->_saleItemsFactory = $_saleItemsFactory;
        $this->_resource = $resourceConnection;
        parent::__construct($context, $data);
    }

    function _prepareLayout()
    {
        $config = $this->getSystemConfig()->getConfig('ms_shopbybrand/brand_details/details_best_seller');
        $right_name = 'brand.best_seller.right';
        $left_name = 'brand.best_seller';
        if($config == 1){
            $this->getLayout()->unsetChild($this->getLayout()->getParentName($right_name),$right_name);
        }elseif ($config == 2){
            $this->getLayout()->unsetChild($this->getLayout()->getParentName($left_name),$left_name);
        }else{
            $this->getLayout()->unsetChild($this->getLayout()->getParentName($left_name),$left_name);
            $this->getLayout()->unsetChild($this->getLayout()->getParentName($right_name),$right_name);
        }
        return parent::_prepareLayout();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getItem($id)
    {
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
        return $product;
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
     * @return $this
     */
    public function getProductBestseller()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $brand = $this->getBrand();
        $productIDs = explode(',', $brand->getProductIdsByBrandId($brand->getId()));

        $numConfig = $this->getSystemConfig()->numberOfProductShow();
        try {
            $collection = $this->_saleItemsFactory->create()
                ->addFieldToFilter('product_id', ['in' => $productIDs]);
            $sfog = $this->_resource->getTableName('sales_order_grid');
            $collection->getSelect()
                ->join(['sfog' => $sfog], 'main_table.order_id = sfog.entity_id AND sfog.status = "complete"',
                    ['billing_name', 'shipping_name']);
            if ($storeId) {
                $collection->addFieldtoFilter('main_table.store_id', $storeId);
            }
            $collection->setOrder('qty_ordered', 'DESC')
                ->setPageSize($numConfig);

            return $collection;
        } catch (\Exception $e) {

        }
    }
}
