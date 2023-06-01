<?php

namespace Smartwave\Filterproducts\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class TrendingProductsList extends \Magento\Catalog\Block\Product\ListProduct {

    protected $_collection;

    protected $categoryRepository;

    protected $_resource;

    protected $eavConfig; 

    public function __construct(
            \Magento\Catalog\Block\Product\Context $context,
            \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
            \Magento\Catalog\Model\Layer\Resolver $layerResolver,
            CategoryRepositoryInterface $categoryRepository,
            \Magento\Framework\Url\Helper\Data $urlHelper,
            \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
            \Magento\Framework\App\ResourceConnection $resource,
            \Magento\Eav\Model\Config $eavConfig,
            array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->_collection = $collection;
        $this->_resource = $resource;
        $this->eavConfig = $eavConfig;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /*protected function _getProductCollection() {
        return $this->getProducts();
    }*/

    public function getProducts($optionIds) {
        $count = $this->getProductCount();
        $category_id = $this->getData("category_id");
        $collection = clone $this->_collection;
        $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);

        if(!$category_id) {
            $category_id = $this->_storeManager->getStore()->getRootCategoryId();
        }
        $category = $this->categoryRepository->get($category_id);
        if(isset($category) && $category) {
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite()
                ->addAttributeToFilter('brand', array('eq' => $optionIds))
                ->addAttributeToFilter('trending', 1);
                //->addAttributeToFilter('sw_featured', 1, 'left')
                //->addCategoriesFilter(['in' => 23]);
                //->addCategoryFilter($category);
        } else {
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite()
                ->addAttributeToFilter('brand', array('eq' => $optionIds))
                ->addAttributeToFilter('trending', 1);
                //->addAttributeToFilter('sw_featured', 1, 'left');
                //->addCategoriesFilter(['in' => 23]);
        }

        $collection->getSelect()
            ->order('rand()')
            ->limit('6');

        return $collection;
    }

    public function getAllOptionIds()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'brand');
        $options = $attribute->getSource()->getAllOptions();
        $optionIds = array();

        foreach ($options as $option) {
            $optionIds[] = $option['value'];
			rsort($optionIds);
        }
        return $optionIds;
    }

    /*public function getLoadedProductCollection($optionIds) {
        return $this->getProducts($optionIds);
    }*/

    public function getProductCount() {
        $limit = $this->getData("product_count");
        if(!$limit)
            $limit = 10;
        return $limit;
    }
}
