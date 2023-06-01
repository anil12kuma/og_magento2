<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;

class Brand extends AbstractModel
{
    /**
     * Identifier of default store view field
     * used for save data by store
     */
    const STORE_VALUE_COLUMN = ['brand_name', 'is_featured','meta_keywords','meta_description','short_description','description', 'is_active', 'sort_order'];
    const BASE_MEDIA_PATH = 'brands';
    const REQUEST_PATH_VIEW_BRAND = 'brand/index/viewbrand';
    protected $_productIds;
    /**
     * @var null
     */
    protected $_brandCollection = null;
    /**
     * store view id.
     *
     * @var int
     */
    protected $_storeViewId = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * value factory.
     *
     * @var \Magestore\Shopbybrand\Model\StoreValueFactory
     */
    protected $_storeValueFactory;
    protected $_storeValueCollectionFactory;
    protected $_storeCollectionFactory;
    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory
     */
    protected $_brandProductsCollectionFactory;
    /*
    * @var \Magestore\Shopbybrand\Model\BrandFactory
    */
    protected $_brandFactory;
    protected $_urlRewriteFactory;
    protected $_objectManager;

    function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magestore\Shopbybrand\Model\ResourceModel\Brand $resource,
        \Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection $resourceCollection,
        \Magestore\Shopbybrand\Model\StoreValueFactory $storeValueFactory,
        \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory $storeValueCollectionFactory,
        \Magestore\Shopbybrand\Model\BrandFactory $brandFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory $brandProductsCollectionFactory,
        \Magestore\Shopbybrand\Model\UrlrewriteFactory $urlrewriteFactory,
        array $data = []
    ){
        $this->_objectManager = $objectManager;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeValueFactory = $storeValueFactory;
        $this->_storeValueCollectionFactory = $storeValueCollectionFactory;
        $this->_brandFactory = $brandFactory;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_urlRewriteFactory = $urlrewriteFactory;
        $this->_brandProductsCollectionFactory = $brandProductsCollectionFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Shopbybrand\Model\ResourceModel\Brand');
    }

    public function _afterLoad()
    {
        parent::_afterLoad();
        $storeId = (int)$this->_request->getParam('store');
        if(!$storeId){
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $storeColumns = self::STORE_VALUE_COLUMN;
        foreach ($storeColumns as $code) {
            $storeValue = $this->_storeValueCollectionFactory->create();
            $collectionData = $storeValue
                ->addFieldToFilter('brand_id', $this->getId())
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('code', $code)
                ->getFirstItem()
                ->getData();
            if($collectionData){
                $this->setData($code, $collectionData['value']);
            }
        }
        return $this;
    }

    /**
     * @param $brand
     *
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addOption($brand)
    {
        $prefix = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getTablePrefix();
        $attributeCode = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getAttributeCode();
        $brandStoreId = 0;
        if ($brand->getOptionId()) {
            if ($brand->getStoreId()) {
                $brandStoreId = $brand->getStoreId();
            }
            $select = $this->getConnection()->select()
                ->from(['eao' => $prefix . 'eav_attribute_option'], ['option_id', 'eaov.value', 'eaov.store_id'])
                ->join(['ea' => $prefix . 'eav_attribute'], 'eao.attribute_id=ea.attribute_id', [])
                ->join(['eaov' => $prefix . 'eav_attribute_option_value'], 'eao.option_id=eaov.option_id', [])
                ->where('ea.attribute_code=?', $attributeCode)
                ->where('eao.option_id=?', $brand->getOptionId())
                ->where('eaov.store_id=?', $brandStoreId);
            $storeValue = $this->getConnection()->fetchAll($select);
            if (count($storeValue)) {
                foreach ($storeValue as $value) {
                    if (isset($value['value']) && $value['value']) {
                        if ($value['value'] == $brand->getBrandName()) {
                            return;
                        } else {
                            $data = [
                                'value' => $brand->getBrandName(),
                            ];
                            $where = [
                                'option_id=?' => $brand->getOptionId(),
                                'store_id=?'  => $brandStoreId,
                            ];
                            $update = $this->getConnection()->update($prefix . 'eav_attribute_option_value', $data,
                                $where);
                        }
                    }
                }
            } else {
                $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
                $attId = $eavAttribute->getIdByCode('catalog_product', $attributeCode);
                $data = [
                    'value' => $brand->getBrandName(),
                    'option_id' => $brand->getOptionId(),
                    'store_id' => $brandStoreId,
                ];
                $select = $this->getConnection()->select()
                    ->from(['eao' => $prefix . 'eav_attribute_option'], ['option_id'])
                    ->join(['ea' => $prefix . 'eav_attribute'], 'eao.attribute_id=ea.attribute_id', [])
                    ->where('ea.attribute_code=?', $attributeCode)
                    ->where('eao.option_id=?', $brand->getOptionId());
                $storeValue = $this->getConnection()->fetchAll($select);
                if (count($storeValue) == 0) {
                    $optionData = [
                        'option_id'    => $brand->getOptionId(),
                        'attribute_id' => $attId,
                        'sort_order'   => 0,
                    ];
                    $option = $this->getConnection()->insert($prefix . 'eav_attribute_option', $optionData);
                }
                try {
                    $update = $this->getConnection()->insert($prefix . 'eav_attribute_option_value', $data);
                } catch (\Exception $e) {
                }
            }
        } else {
            $attributeId = $this->_objectManager->create('Magento\Eav\Model\Config')
                ->getAttribute('catalog_product', $attributeCode)->getId();
            $setup = $this->_categorySetupFactory->create([
                'resourceName' => 'catalog_setup',
                'setup'        => $this->_setup,
            ]);
            $option['attribute_id'] = $attributeId;
            if ($brand->getStoreId()) {
                $option['value']['option'][$brand->getStoreId()] = $brand->getBrandName();
            } else {
                $option['value']['option'][0] = $brand->getBrandName();
            }
            $setup->addAttributeOption($option);
            //get option id
            $select = $this->getConnection()->select()
                ->from(['eao' => $prefix . 'eav_attribute_option'], ['option_id', 'eaov.value', 'eaov.store_id'])
                ->join(['ea' => $prefix . 'eav_attribute'], 'eao.attribute_id=ea.attribute_id', [])
                ->join(['eaov' => $prefix . 'eav_attribute_option_value'], 'eao.option_id=eaov.option_id', [])
                ->where('ea.attribute_code=?', $attributeCode)
                ->where('eaov.value=?', $brand->getBrandName())
                ->where('eaov.store_id=?', $brandStoreId);
            $option = $this->getConnection()->fetchAll($select);
            if (count($option)) {
                $optionId = $option[0]['option_id'];
                return $optionId;
            }
        }

        return null;
    }

    /**
     * Validate data
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string[] - return true if validation passed successfully. Array with errors description otherwise
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateData(DataObject $dataObject)
    {
        $result = [];

        // Logic validate, example:
        /*
        $fromDate = $toDate = null;
        if ($dataObject->hasFromDate() && $dataObject->hasToDate()) {
            $fromDate = $dataObject->getFromDate();
            $toDate = $dataObject->getToDate();
        }

        if ($fromDate && $toDate) {
            $fromDate = new \DateTime($fromDate);
            $toDate = new \DateTime($toDate);

            if ($fromDate > $toDate) {
                $result[] = __('End Date must follow Start Date.');
            }
        }

        if ($dataObject->hasWebsiteIds()) {
            $websiteIds = $dataObject->getWebsiteIds();
            if (empty($websiteIds)) {
                $result[] = __('Please specify a website.');
            }
        }
        if ($dataObject->hasCustomerGroupIds()) {
            $customerGroupIds = $dataObject->getCustomerGroupIds();
            if (empty($customerGroupIds)) {
                $result[] = __('Please specify Customer Groups.');
            }
        }
        */

        return !empty($result) ? $result : true;
    }

    /**
     * before save check store fields change or not
     */
    public function beforeSave()
    {
        if(!$this->getId()){
            $this->setData('new_brand', true);
        }
        // prepare attribute data for store
        if ($this->getCurrentStoreId()) {
            $storeColumns = self::STORE_VALUE_COLUMN;
            $data = $this->getData();
            foreach ($storeColumns as $column) {
                if (isset($data['use_default']) && isset($data['use_default'][$column])) {
                    // if use_default = false, flag remove column value
                    if($data['use_default'][$column] == 'false'){
                        $this->setData($column.'_use_default', 'true');
                    }
                }else{
                    // use_default = true, set custom column data, unset for default column
                    $this->setData($column.'_value', $this->getData($column));
                    $this->unsetData($column);
                }
            }
        }
        return parent::beforeSave();
    }

    /**
     * after save, save fields changed to database
     */
    public function afterSave()
    {
        $storeColumns = self::STORE_VALUE_COLUMN;
        $brandId = $this->getId();
        // save attribute data by store
        if ($storeId = $this->getCurrentStoreId()) {
            foreach ($storeColumns as $code) {
                $storeValueModel = $this->_storeValueFactory->create();
                $storeValueCollection = $this->_storeValueCollectionFactory->create();
                $collectionData = $storeValueCollection
                    ->addFieldToFilter('brand_id', $brandId)
                    ->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('code', $code)
                    ->getFirstItem()
                    ->getData();

                if($value = $this->getData($code.'_value')) {
                    // update database
                    if(!empty($collectionData) && isset($collectionData['value'])) {
                        $storeValueModel->load($collectionData['value_id']);
                        $storeValueModel->setValue($value)->save();
                    }else{
                        $data = [
                            'brand_id' => $brandId,
                            'store_id' => $storeId,
                            'code' => $code,
                            'value' => $value
                        ];
                        $storeValueModel->setData($data)->save();
                    }
                }

                if($this->getData($code.'_use_default') == 'true') {
                    // remove database
                    if(!empty($collectionData) && isset($collectionData['value'])) {
                        $storeValueModel->load($collectionData['value_id']);
                        $storeValueModel->delete();
                    }
                }
//                    $storeValue->loadAttributeValue($brandId, $storeViewId, $code);
//                    if ($this->getData($code.'_in_store')) {
////                        if ($code == 'banner' && $this->getData('delete_image')) {
////                            $storeValue->delete();
////                        } else {
//                            $storeValue->setValue($this->getData($code.'_value'))->save();
////                        }
//                    } elseif ($storeValue && $storeValue->getId()) {
//                        $storeValue->delete();
//                    }
            }
        }
        return parent::afterSave();
    }



    /**
     * remove store value by brand_id
     *
     * @return $this
     */
    public function beforeDelete()
    {
        $brandId = $this->getId();
        $model = $this->_storeValueFactory->create();
        $collection = $model->getCollection()
            ->addFieldToFilter('brand_id', $brandId);
        if ($collection->count() > 0) {
            foreach ($collection as $data) {
                $data->delete();
            }
        }
        parent::beforeDelete();
        return $this;
    }

    public function getProductIdsByBrandId($id)
    {
        $brandCollection = $this->_brandProductsCollectionFactory->create();
        $brandCollection->addFieldToFilter('brand_id', ['eq' => $id]);
        $brandCollection->addOrder('position', strtoupper('asc'));
        $productIds = implode(',', $brandCollection->getColumnValues('product_id'));
        return $productIds;
    }

    /**
     * @return null
     */
    public function getBrandCollection()
    {
        if (is_null($this->_brandCollection)) {
            $store = $this->_storeManager->getStore()->getId();
            $systemConfigObj = $this->_objectManager->create('\Magestore\Shopbybrand\Model\SystemConfig');
            $showNumberOfProducts = $systemConfigObj->isDisplayProductNumber();
            $onlyBrandHaveProduct = $systemConfigObj->isDisplayBrandHaveProduct();
            $array = ['brand_name'];

            /** @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection $collection */
            $collection = $this->getCollection()
                //@@TODO fix this func
                //->setStoreId($store, $array)
//                ->setOrder('position_brand', 'DESC')
                ->setOrder('sort_order', 'ASC')
                ->addFieldToFilter('main_table.is_active', ['eq' => 1]);

            if ($showNumberOfProducts || $onlyBrandHaveProduct) {

                $collection->getSelect()->joinLeft(
                    ['ms_brand_products' => $collection->getTable('ms_brand_products')],
                    'main_table.id=ms_brand_products.brand_id AND ms_brand_products.visibility_status != 1',
                    []
                )
                ->group('main_table.id')
                ->columns([
                    'number_product' => 'SUM(IF( ms_brand_products.product_id > 0, 1, 0 ))',
                    'product_ids' => 'GROUP_CONCAT(ms_brand_products.product_id)',
                    'category_ids' => 'GROUP_CONCAT(ms_brand_products.category_ids)'
                ]);
            }

            if ($onlyBrandHaveProduct) {
                foreach ($collection as $key => $item){
                    if($item['number_product'] == 0){
                        $collection->removeItemByKey($key);
                    }else if($item['number_product'] >= 1){
                        $product_ids = explode(',',$item->getData('product_ids'));
                        foreach ($product_ids as $product_id){
                            $productData = $this->_objectManager->create('\Magento\Catalog\Model\Product')->load((int)$product_id);
                            if($productData->getStatus() == 2){
                                $collection->removeItemByKey($key);
                            }
                        }
                    }
                }
            }
            $this->_brandCollection = $collection;
        }
        return $this->_brandCollection;
    }

    /*
     * @return array
     */
    public function getBrandsData()
    {
        if ($this->getBrandCollectionData()) {
            return $this->getBrandCollectionData();
        }

        $brandData = $this->getBrandCollection();
        $array = [];
        foreach ($brandData as $brand) {
            
            $brandId = $brand->getData('id');
            $connection = $this->resourceConnection->getConnection();
            $sql = "SELECT product_id FROM `ms_brand_products` WHERE `brand_id` = '".$brandId."' LIMIT 50";
            $skuList = array();
            $result = $connection->fetchAll($sql);
            foreach ($result as $key => $value) {
                $skuList[] = $value['product_id'];
            }
            $List = implode(', ', $skuList);
            
            $productCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
            
            $collection = $productCollection->addAttributeToSelect('')
                        ->addFieldToFilter('entity_id', array('in' => $List));
            $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            
            if(count($collection) > 0){           
                $data['brand_id'] = $brand->getData('id');
                $data['brand_name'] = $brand->getData('brand_name');
                $data['url_key'] = $brand->getData('url_key');
                $data['logo'] = $brand->getData('logo');
                if($brand->getData('category_ids')){
                    $category_ids = implode(",",array_unique(explode(',',$brand->getData('category_ids'))));
                }else{
                    $category_ids = [];
                }
                $data['category_ids'] = $category_ids;
                $data['number_product'] = count($collection);
                $array[] = $data;
            }
        }
        $this->setBrandCollectionData($array);
        return $array;
    }

    /**
     *
     */
    public function updateUrlKey()
    {
        $id = $this->getId();
        $url_key = $this->getData('url_key');
        try {
            if ($this->getCurrentStoreId()) {
                $urlrewrite = $this->_urlRewriteFactory->create()->loadByRequestPath($url_key, $this->getCurrentStoreId());
                $urlrewrite->setData("request_path", $this->getData('url_key'));
                $urlrewrite->setData("target_path", 'brand/index/viewbrand/brand_id/' . $id);
                $urlrewrite->setData("store_id", $this->getCurrentStoreId());
                try {
                    $urlrewrite->save();
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            } else {
                $stores = $this->_objectManager->create('Magento\Store\Model\ResourceModel\Store\Collection')
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('store_id', ['neq' => 0]);
                foreach ($stores as $store) {
                    $rewrite = $this->_urlRewriteFactory->create()->loadByRequestPath($url_key, $store->getId());
                    $rewrite->setData("request_path", $this->getData('url_key'));
                    $rewrite->setData("target_path", 'brand/index/viewbrand/brand_id/' . $id);
                    try {
                        $rewrite->setData('store_id', $store->getId())->save();
                    } catch (\Exception $e) {
                        $this->_logger->critical($e);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return mixed
     */
    public function getProIds()
    {
        if (!$this->_productIds) {
            $attributeCode = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getAttributeCode();
            $id = $this->_objectManager->get('Magento\Framework\App\RequestInterface')->getParam('brand_id');
            $brand = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand')->load($id);
            $optionId = $brand->getOptionId();
            $collection = $this->_objectManager
                ->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                ->addAttributeToSelect($attributeCode)
                ->addAttributeToFilter($attributeCode, $optionId);
            $this->_productIds = $collection->getAllIds();
        }

        return $this->_productIds;
    }

    /**
     * Get array of product ids which are matched by brand
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];

            $attributeCode = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getAttributeCode();
            $optionId = $this->getOptionId();
            $collection = $this->_objectManager
                ->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                ->addAttributeToSelect($attributeCode)
                ->addAttributeToFilter($attributeCode, $optionId);
            $this->_productIds = $collection->getAllIds();
        }
        return $this->_productIds;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getFeaturedProductIds($brand_id = null)
    {
        /** @var \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\Collection $brandProductCollection */
        $brandProductCollection = $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\Collection');
        if($brand_id){
            $brandProductCollection->addFieldToFilter('brand_id', $brand_id);
        }

        $defaultProduct = $this->getProIds();
        if($defaultProduct){
            $brandProductCollection->addFieldToFilter('product_id', ['in' => $defaultProduct]);
        }

        $visibility = $this->_objectManager->create('Magento\Catalog\Model\Product\Visibility')->getVisibleInCatalogIds();
        $brandProductCollection
            ->addFieldToFilter('visibility_status', ['in' => $visibility])
            ->addFieldToFilter('is_featured', 1);

        $array = [];
        foreach ($brandProductCollection as $item) {
            $array[$item->getProductId()] = 0;
        }

        $productIds = array_unique(array_keys($array));
//        $_products = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
//            ->addAttributeToSelect(['name', 'product_url', 'small_image'])
//            ->addAttributeToFilter('entity_id', ['in' => $productIds]);

        return $productIds;
    }

    /**
     * @param $requestPath
     * @param $storeId
     *
     * @return mixed
     */
    public function loadByRequestPath($requestPath, $storeId)
    {
        $model = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');
        $collection = $model->getCollection();
        $collection->addFieldToFilter('request_path', $requestPath)
            ->addFieldToFilter('store_id', $storeId);
        if ($collection->getSize()) {
            $model = $collection->getFirstItem();
        }

        return $model;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteUrlRewrite() {
        if ($this->getId()) {
            $stores = $this->_storeCollectionFactory->create()
                ->addFieldToFilter('is_active', 1);
            ;
            foreach ($stores as $store) {
                $url = $this->loadByIdPath(self::REQUEST_PATH_VIEW_BRAND.'/brand_id/'. $this->getId(), $store->getId());
                if ($url->getId()) {
                    $url->delete();
                }
            }
        }
    }

    /**
     * @param $idPath
     * @param $storeId
     * @return mixed
     */
    public function loadByIdpath($idPath, $storeId){

        $model = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()
            ->addFieldToFilter('target_path', $idPath)
            ->addFieldToFilter('store_id', $storeId)
            ->getFirstItem();
        return $model;
    }

    /**
     * @return array
     */
    public function getArrayProductIds()
    {
        if($this->getId()){
            return explode(',', $this->getProductIdsByBrandId($this->getId()));
        }
        return [];
    }

    /**
     * Get category products collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
//    public function getProductCollection()
//    {
//        var_dump('1231'); die;
//        $productIds = explode(',', $this->getProductIds());
//
//        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('entity_id', ['in' => $productIds]);
//
//        return $collection;
//    }

    /**
     *
     *
     * @return void
     */
    function setProductIds($proIds)
    {
        $brandId = $this->getId();
        if($brandId){
            $brandProducts = $this->_objectManager->create('Magestore\Shopbybrand\Model\BrandProducts');
            if(is_array($proIds) && !empty($proIds)){
                foreach ($proIds as $product_id){
                    $brandProducts->updateProductDataByBrand($product_id, $brandId);
                }
            } else {
                if($proIds != ''){
                    $brandProducts->updateProductDataByBrand($proIds, $brandId);
                }
            }
        }
    }

}