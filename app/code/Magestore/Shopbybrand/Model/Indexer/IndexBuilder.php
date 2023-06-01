<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
//use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magestore\Shopbybrand\Model\Brand;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder
{
    const SECONDS_IN_DAY = 86400;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var BrandCollectionFactory
     */
    protected $brandCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magestore\Shopbybrand\Model\BrandFactory
     */
    protected $brandFactory;

    /**
     * @var Product[]
     */
    protected $loadedProducts;

    /**
     * @var int
     */
    protected $batchCount;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magestore\Shopbybrand\Helper\Brand
     */
    protected $_brandHelper;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $_brandCollectionFactory;


    /**
     * @param BrandCollectionFactory $brandCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magestore\Shopbybrand\Model\BrandFactory $brandFactory
     * @param \Magestore\Shopbybrand\Helper\Brand $brandHelper
     * @param \Magestore\Shopbybrand\Model\BrandProductsFactory $brandProductsFactory
     * @param int $batchCount
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        BrandCollectionFactory $brandCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magestore\Shopbybrand\Model\BrandFactory $brandFactory,
        \Magestore\Shopbybrand\Helper\Brand $brandHelper,
        \Magestore\Shopbybrand\Model\BrandProductsFactory $brandProductsFactory,
        $batchCount = 1000
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->brandCollectionFactory = $brandCollectionFactory;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->brandFactory = $brandFactory;
        $this->batchCount = $batchCount;
        $this->_brandHelper = $brandHelper;
        $this->_brandProductsFactory = $brandProductsFactory;
    }

    /**
     * Reindex by id
     *
     * @param int $id
     * @return void
     * @api
     */
    public function reindexById($id)
    {
        $this->reindexByIds([$id]);
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->doReindexByIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     * @return void
     */
    protected function doReindexByIds($ids)
    {

    }

    /**
     * Full reindex
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        $this->deleteOldData();
        foreach ($this->getAllBrands() as $brand) {
            $this->updateBrandProductData($brand);
        }
    }

    /**
     * @param string $tableName
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * @param Rule $rule
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateBrandProductData(Brand $brand)
    {
        $brandId = $brand->getId();
        $this->connection->delete(
            $this->getTable('ms_brand_products'),
            $this->connection->quoteInto('brand_id=?', $brandId)
        );

        if (!$brand->getIsActive()) {
            return $this;
        }

        \Magento\Framework\Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $brand->getMatchingProductIds();
        \Magento\Framework\Profiler::stop('__MATCH_PRODUCTS__');

        $rows = [];

        foreach ($productIds as $productId) {

            $productInfo = $this->getProductInfo($productId);
            $rows[] = [
                'brand_id' => $brandId,
                'product_id' => $productId,
                'is_featured' => 0,
                'visibility_status' => $productInfo['visibility_status'],
                'position' => 0,
                'category_ids' => $productInfo['category_ids']
            ];

            if (count($rows) == $this->batchCount) {
                $this->connection->insertMultiple($this->getTable('ms_brand_products'), $rows);
                $rows = [];
            }

        }
        if (!empty($rows)) {
            $this->connection->insertMultiple($this->getTable('ms_brand_products'), $rows);
        }

        return $this;
    }

    protected function getProductInfo($id)
    {
        $result = [];
        $product = $this->productFactory->create()->load($id);
        if($product->getId()){
            $result['visibility_status'] = $product->getVisibility();
            $cats = $product->getCategoryIds();
            if(count($cats) ){
                $cats = implode(",",$cats);
            }else{
                $cats = '';
            }
            $result['category_ids'] = $cats;
        }
        return $result;
    }


    /**
     * Clean rule price index
     *
     * @return $this
     */
    protected function deleteOldData()
    {
        $this->connection->delete($this->getTable('ms_brand_products'));
        return $this;
    }

    /**
     * Get active brands
     *
     * @return array
     */
    protected function getActiveBrands()
    {
        return $this->brandCollectionFactory->create()
            ->addFieldToFilter('is_active', 1);
    }

    /**
     * Get active rules
     *
     * @return array
     */
    protected function getAllBrands()
    {
        return $this->brandCollectionFactory->create();
    }

    /**
     * @param int $productId
     * @return Product
     */
    protected function getProduct($productId)
    {
        if (!isset($this->loadedProducts[$productId])) {
            $this->loadedProducts[$productId] = $this->productFactory->create()->load($productId);
        }
        return $this->loadedProducts[$productId];
    }

    /**
     * @param \Exception $e
     * @return void
     */
    protected function critical($e)
    {
        $this->logger->critical($e);
    }

}
