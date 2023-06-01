<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Shopbybrand\Helper;

/**
 * Helper Data
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Brand extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var null
     */
    protected $_storeId = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magestore\Shopbybrand\Model\BrandFactory
     */
    protected $_brandFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\Brand
     */
    protected $_resourceBrand;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection
     */
    protected $_brandCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $_catCollection;

    /**
     * Brand constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Shopbybrand\Model\BrandFactory $_brandFactory
     * @param \Magestore\Shopbybrand\Model\ResourceModel\Brand $_resourceBrand
     * @param \Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection $brandCollection
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $catCollection
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Shopbybrand\Model\BrandFactory $_brandFactory,
        \Magestore\Shopbybrand\Model\ResourceModel\Brand $_resourceBrand,
        \Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection $brandCollection,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $catCollection,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->_backendUrl = $backendUrl;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_brandFactory = $_brandFactory;
        $this->_resourceBrand = $_resourceBrand;
        $this->_brandCollection = $brandCollection;
        $this->_catCollection = $catCollection;
        $this->_resource = $resource;
        $this->_objectManager = $objectManager;
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
     * @return mixed|string
     */
    public function getAttributeCode()
    {
        $attributeCode = $this->scopeConfig->getValue('ms_shopbybrand/general/attribute_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $attributeCode ? $attributeCode : 'manufacturer';
    }

    /**
     * @param $urlKey
     *
     * @return mixed|string
     */
    public function refineUrlKey($urlKey)
    {
        for ($i = 0; $i < 5; $i++) {
            $urlKey = str_replace("  ", " ", $urlKey);
        }
        $chars = [
            'Š' => 'S',
            'š' => 's',
            'Đ' => 'Dj',
            'đ' => 'dj',
            'Ž' => 'Z',
            'ž' => 'z',
            'Č' => 'C',
            'č' => 'c',
            'Ć' => 'C',
            'ć' => 'c',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ý' => 'y',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y',
            'Ŕ' => 'R',
            'ŕ' => 'r',
        ];
        $newUrlKey = strtr($urlKey, $chars);
        $newUrlKey = str_replace(" ", "-", $newUrlKey);
        $newUrlKey = htmlspecialchars(strtolower($newUrlKey));
        $newUrlKey = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $newUrlKey);

        return $newUrlKey;
    }

    /**
     * @param $brand
     *
     * @return array|string
     */
    public function getCategoryIdsByBrand($brand)
    {
        $catIds = [];
        $model = $this->_brandFactory->create();
        $collection = $model->getCollection()->addFieldToFilter('id', $brand->getId());
        $product_ids = $model->getProductIdsByBrandId($brand->getId());

        $collection->getSelect()
            ->join(['category_product' => $this->_resource->getTableName('catalog_category_product')],
                'FIND_IN_SET(category_product.product_id, "'.$product_ids.'")', 'category_id')
            ->group('category_id');

        if ($collection->getSize()) {
            $catIds = $collection->getCategoryIdsFromProducts('category_product');
        }
        $catIds = implode(',', $catIds);
        return $catIds;
    }

    /**
     * @param $productIds
     * @param $brand
     * @param $storeId
     */
    public function updateProductsBrand($productIds, $brand, $storeId)
    {
        $oldProductIds = $brand->getProductIdsByBrandId($brand->getId());
        $attributeCode = $this->getAttributeCode();
        $oldAttributeData = [
            $attributeCode => null,
        ];
        $this->_objectManager->get('Magento\Catalog\Model\Product\Action')->updateAttributes(explode(',', $oldProductIds), $oldAttributeData, $storeId);

        $newAttributeData = [$attributeCode => $brand->getOptionId()];
        $this->_objectManager->get('Magento\Catalog\Model\Product\Action')->updateAttributes($productIds, $newAttributeData, $storeId);
    }

    /**
     * @return mixed
     */
    public function getTablePrefix()
    {
        $tableName = $this->_resourceBrand->getTable('brand');
        $prefix = str_replace('brand', '', $tableName);
        return $prefix;
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->_storeId = $this->_storeManager->getStore()->getId();
        }

        return $this->_storeId;
    }

    /**
     * @param null $catids
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getParentCategories($catids = null)
    {
        $cats = [];
        $parentIds = [];
        $children = [];
        if (is_null($catids)) {
            $catids = $this->_brandCollection
                //@@TODO fix this func
//                ->setStoreId($this->getStoreId())
                ->getAllCategories();
            $catids = implode(",", $catids);
            $catids = explode(",", $catids);
            $catids = array_unique($catids);
        }
        $catRootId = $this->_storeManager->getStore()->getRootCategoryId();
        unset($catids[array_search($catRootId, $catids)]);
        $categories = $this->_catCollection
            ->setStoreId($this->getStoreId())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('level', ['gteq' => 2])
            ->addFieldToFilter('entity_id', ['in' => $catids]);
        foreach ($categories as $category) {
            $parents = $category->getParentIds();
            if (count(array_intersect($parents, $catids)) == 0) {
                $parentIds[$category->getId()] = $category;
            } else {
                foreach (array_intersect($parents, $catids) as $parentId) {
                    $children[$parentId][] = $category;
                }
            }
        }
        $cats['parent'] = $parentIds;
        $cats['children'] = $children;
        return $cats;
    }

    /**
     * @param $option
     *
     * @return mixed
     */
    public function getOptionData($option)
    {
        $urlKey = $option['value'];
        $data['brand_name'] = $option['value'];
        $data['page_title'] = $option['value'];
        $data['meta_keywords'] = $option['value'];
        $data['meta_description'] = $option['value'];
        $data['option_id'] = $option['option_id'];
        $data['status'] = 1;
        $data['created_time'] = date('Y:m:d h:i:s');
        $data['update_time'] = date('Y:m:d h:i:s');
        $data['url_key'] = $this->refineUrlKey($urlKey);

        return $data;
    }

    /**
     * @param $option
     */
    public function insertBrandFromOption($option)
    {
        if (isset($option['store_id'])) {
            $data = $this->getOptionData($option);
            $model = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand')->load($option['option_id'], 'option_id');
            $model->addData($data);
            $urlKey = $model->getUrlKey();
            $urlRewrite = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand')->loadByRequestPath($urlKey, $option['store_id']);
            if (!$model->getId()) {
                if ($urlRewrite->getId()) {
                    $urlKey = $urlKey . '-2';
                    $model->setData('url_key', $urlKey);
                }
            }
            $model->setStoreId($option['store_id'])->save();

            // save product_ids && category_ids
//            $productIds = $this->getProductIdsByBrand($model);
//            if (is_string($productIds) && $productIds != '') {
//                $productIds = explode(',', $productIds);
//                $model->setProductIds($productIds);
//            }
//
//            $categoryIds = $this->getCategoryIdsByBrand($model);
//            if (is_string($categoryIds) && $categoryIds) {
//                $model->setCategoryIds($categoryIds)->save();
//            }

            //update url_key
            if ($option['store_id'] == 0) {
                $model->updateUrlKey();
            }
        }
    }

    /**
     * @param $productIds
     * @param $brand
     */
    public function updateProductsForBrands($productIds, $brand)
    {

        //@@TODO check again
        $brands = $this->_brandFactory->create()
            ->getCollection()
            ->setOrder('sort_order', 'DESC')
            ->getFirstItem();

        if ($brands->getOptionId() == null) {
            $brandCollection = $this->_brandFactory->create()
                ->getCollection()
                ->addFieldToFilter('id', ['neq' => $brand->getId()]);
            foreach ($brandCollection as $br) {
                $brandProductIds = explode(',', $br->getProductIdsByBrandId($br->getId()));
                $oldSize = count($brandProductIds);
                $brandProductIds = array_diff($brandProductIds, $productIds);
                $newSize = count($brandProductIds);
                if ($oldSize > $newSize) {
                    $br->setProductIds(implode(',', $brandProductIds));
                    $br->save();
                }
            }
            if ($brands->getData('product_ids')) {
                $brands->setData('product_ids',
                    $brands->getData('product_ids') . ',' . implode(',', $productIds))->save();
            } else {
                $brands->setProductIds(implode(',', $productIds));
                $brands->save();
            }
        } else {
            $brandCollection = $this->_brandFactory->create()
                ->getCollection()
                ->addFieldToFilter('id', ['neq' => $brand->getId()]);
            foreach ($brandCollection as $br) {
                $brandProductIds = explode(',', $br->getData('product_ids'));
                $oldSize = count($brandProductIds);
                $brandProductIds = array_diff($brandProductIds, $productIds);
                $newSize = count($brandProductIds);
                if ($oldSize > $newSize) {
                    $br->setProductIds(implode(',', $brandProductIds));
                    $br->save();
                }
            }
            if ($brand->getData('product_ids')) {
                $brand->setData('product_ids',
                    $brand->getData('product_ids') . ',' . implode(',', $productIds))->save();
            } else {
                $brand->setProductIds(implode(',', $productIds));
                $brand->save();
            }
        }
    }



    /**
     * @param $brand
     *
     * @return string
     */
    public function getProductIdsByBrand($brand)
    {
        $productIds = '';
        $attributeCode = $this->getAttributeCode();
        $optionId = $brand->getOptionId();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter($attributeCode, $optionId);
        if($collection->getSize()){
            $productIds = implode(",", $collection->getAllIds());
        }

        return $productIds;
    }

    /**
     *
     */
    public function updateBrandsFormCatalog()
    {
        $defaultOptionBrands = $this->_resourceBrand->getCatalogBrand(true);
        $storeOptionBrands = $this->_resourceBrand->getCatalogBrand(false);
        foreach ($defaultOptionBrands as $option) {
            $this->insertBrandFromOption($option);
        }
        foreach ($storeOptionBrands as $option) {
            $defaultBrand = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand')->load($option['option_id'], 'option_id');
            $brandValue = $this->_objectManager->create('Magestore\Shopbybrand\Model\StoreValue')->loadAttributeValue($defaultBrand->getId(), $option['store_id'], 'name');
            if ($brandValue->getValue() != $option['value']) {
                $brandValue->setData('value', $option['value'])
                    ->save();
            }
        }
    }

    /**
     * @param $id
     */
    public function reindexBrandCategory($id)
    {
        $brand = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand')->load($id);
        $categoryIds = $this->getCategoryIdsByBrand($brand);

        //@TODO fix get categoryids
        if ($categoryIds != $brand->getCategoryIds()) {
            $brand->setCategoryIds($categoryIds)
                ->save();
        }
    }

    public function getBrandIds()
    {
        /** @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection $brandCollection */
        $brandCollection = $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection');
        $brandCollection->addFieldToSelect('id');
        
        return $brandCollection->getAllIds();
    }

    /**
     * @param $id
     */
    public function reindexBrandUrl($id)
    {
        $brand = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand')->load($id);
        $brand->updateUrlKey();
    }
}
