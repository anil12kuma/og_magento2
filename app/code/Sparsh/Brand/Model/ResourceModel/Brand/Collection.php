<?php
/**
 * Class Collection
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Model\ResourceModel\Brand;

use Sparsh\Brand\Model\ResourceModel\AbstractCollection;
use Sparsh\Brand\Api\Data\BrandInterface;
use Sparsh\Brand\Model\Brand;

/**
 * Class Collection
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Collection extends AbstractCollection
{
    /**
     * Primary field name of table
     *
     * @var string
     */
    protected $_idFieldName = 'brand_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Sparsh\Brand\Model\Brand::class,
            \Sparsh\Brand\Model\ResourceModel\Brand::class
        );
        $this->_map['fields']['brand_id'] = 'main_table.brand_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(BrandInterface::class);
        $this->performAfterLoad('sparsh_brand_store', $entityMetadata->getLinkField());
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }

    protected function _renderFiltersBefore()
    {
        $entityMetadata = $this->metadataPool->getMetadata(BrandInterface::class);
        $this->joinStoreRelationTable('sparsh_brand_store', $entityMetadata->getLinkField());
    }

}
