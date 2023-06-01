<?php

namespace Rb\BrandReviewSlider\Block\Widget;

use Magento\Widget\Block\BlockInterface;

class Slider  extends \Magento\Framework\View\Element\Template implements BlockInterface
{
    protected $_template = "widget/slider.phtml";
    protected $_productCollectionFactory;
    protected $eavConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_reviewCollectionFactory = $reviewCollectionFactory;
        $this->_storeManager = $storeManager;
		$this->product = $product;
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    public function getReviewsCollection($productId = Null)
    {
        $currentStoreId = $this->getCurrentStoreId();
        $collection = $this->_reviewCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addStoreFilter($currentStoreId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();
        $ReviewCollection = $collection->addEntityFilter('product',  array('eq' => array($productId)));
        return $ReviewCollection;
    }

    public function getProductCollection()
    {
        $optionId = $this->getOptionId();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('brand', array('notnull' => true));
        $collection->addAttributeToFilter('brand', array('eq' => $optionId));
        $collection->setPageSize(10)->setCurPage(1);
        return $collection;
    }
    public function getOptionId()
    {
        $brandName = $this->getBrand();
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'brand');
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option) {
            if ($brandName == $option['label']) {
                $optionId = $option['value'];
                return $optionId;
            }
        }
    }

    public function getProduct($id)
    {
        return $this->product->create()->load($id);
    }

    public function getHeading()
    {
        return $this->getData('title');
    }
    public function getBrand()
    {
        return $this->getData('brand');
    }
}
