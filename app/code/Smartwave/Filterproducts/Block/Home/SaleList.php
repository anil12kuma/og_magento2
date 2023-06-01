<?php

namespace Smartwave\Filterproducts\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class SaleList extends \Magento\Catalog\Block\Product\ListProduct
{

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

    public function getProducts($optionIds, $limit = "")
    {
        $count = $this->getProductCount();
        // $category_id = $this->getData("category_id");
        $collection = clone $this->_collection;
        $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);

        // if(!$category_id) {
        //     $category_id = $this->_storeManager->getStore()->getRootCategoryId();
        // }
        // $category = $this->categoryRepository->get($category_id);
        // $now = date('Y-m-d');
        // if(isset($category) && $category) {
        //     $collection->addMinimalPrice()
        //         ->addFinalPrice()
        //         ->addTaxPercents()
        //         ->addAttributeToSelect('name')
        //         ->addAttributeToSelect('image')
        //         ->addAttributeToSelect('small_image')
        //         ->addAttributeToSelect('thumbnail')
        //         ->addAttributeToSelect('special_from_date')
        //         ->addAttributeToSelect('special_to_date')
        //         ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
        //         ->addAttributeToFilter('special_price', ['neq' => ''])
        //         ->addAttributeToFilter('brand', array('eq' => $optionIds))
        //         ->addAttributeToFilter('deals', 1)
        //         //->addCategoryFilter($category)
        //         ->addAttributeToFilter(
        //             'special_from_date',
        //             ['date' => true, 'to' => $this->getEndOfDayDate()],
        //             'left')
        //         ->addAttributeToFilter(
        //             'special_to_date',
        //             [
        //                 'or' => [
        //                     0 => ['date' => true, 'from' => $this->getStartOfDayDate()],
        //                     1 => ['is' => new \Zend_Db_Expr('null')],
        //                 ]
        //             ],
        //             'left')
        //         ->addAttributeToSort(
        //             'news_from_date',
        //             'desc')
        //         ->addStoreFilter($this->getStoreId())
        //         ->setCurPage(1);
        // } else {
        //     $collection->addMinimalPrice()
        //         ->addFinalPrice()
        //         ->addTaxPercents()
        //         ->addAttributeToSelect('name')
        //         ->addAttributeToSelect('image')
        //         ->addAttributeToSelect('small_image')
        //         ->addAttributeToSelect('thumbnail')
        //         ->addAttributeToFilter('special_price', ['neq' => ''])
        //         ->addAttributeToSelect('special_from_date')
        //         ->addAttributeToSelect('special_to_date')
        //         ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
        //         ->addAttributeToFilter('brand', array('eq' => $optionIds))
        //         ->addAttributeToFilter('deals', 1)
        //         ->addAttributeToFilter(
        //             'special_from_date',
        //             ['date' => true, 'to' => $this->getEndOfDayDate()],
        //             'left')
        //         ->addAttributeToFilter(
        //             'special_to_date',
        //             [
        //                 'or' => [
        //                     0 => ['date' => true, 'from' => $this->getStartOfDayDate()],
        //                     1 => ['is' => new \Zend_Db_Expr('null')],
        //                 ]
        //             ],
        //             'left')
        //         ->addAttributeToSort(
        //             'news_from_date',
        //             'desc')
        //         ->addStoreFilter($this->getStoreId())
        //         ->setCurPage(1);
        // }

        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('thumbnail')
            // ->addAttributeToSelect('special_from_date')
            // ->addAttributeToSelect('special_to_date')
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addAttributeToFilter('brand', array('eq' => $optionIds))
            // ->addAttributeToFilter('parent_id', null)
            ->addAttributeToFilter('special_price', ['neq' => ''])
            // ->addAttributeToFilter('deals', 1)
            // ->addAttributeToFilter(
            //     'special_from_date',
            //     ['date' => true, 'to' => $this->getEndOfDayDate()],
            //     'left')
            // ->addAttributeToFilter(
            //     'special_to_date',
            //     [
            //         'or' => [
            //             0 => ['date' => true, 'from' => $this->getStartOfDayDate()],
            //             1 => ['is' => new \Zend_Db_Expr('null')],
            //         ]
            //     ],
            //     'left')
            ->addAttributeToSort(
                'news_from_date',
                'desc'
            )
            ->addStoreFilter($this->getStoreId())
            ->setCurPage(1);

        $pageLimit = $limit ? $limit : 8;

        $collection->getSelect()
            ->order('rand()')
            ->limit($pageLimit);
        // dd($collection->getData());

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

    /*public function getLoadedProductCollection() {
    return $this->getProducts();
    }*/

    public function getProductCount()
    {
        $limit = $this->getData("product_count");
        if (!$limit)
            $limit = 10;
        return $limit;
    }

    public function getStartOfDayDate()
    {
        return $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    }

    public function getEndOfDayDate()
    {
        return $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}