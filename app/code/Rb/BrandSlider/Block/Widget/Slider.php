<?php

namespace Rb\BrandSlider\Block\Widget;

use Magento\Widget\Block\BlockInterface;

class Slider  extends \Magento\Framework\View\Element\Template implements BlockInterface
{
    protected $_template = "widget/slider.phtml";
    protected $_productCollectionFactory;
    protected $eavConfig;
    public $productRepository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    public function getProductCollection($optionIds)
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('brand', array('notnull' => true));
        $collection->addAttributeToFilter('brand', array('eq' => $optionIds));
        $collection->addAttributeToSort('brand');
        $collection->addAttributeToFilter('is_brand_slider', 1);
        $collection->getSelect()
            ->limit(10);
        return $collection;
    }
    public function getAllOptionIds()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'brand');
        $options = $attribute->getSource()->getAllOptions();
        $optionIds = array();

        foreach ($options as $option) {
            $optionIds[] = $option['value'];
        }
        return $optionIds;
    }
    public function getProductById($id)
    {
        return $this->productRepository->getById($id);
    }

    public function getHeading()
    {
        return $this->getData('title');
    }
    public function getDescription()
    {
        return $this->getData('description');
    }
}
