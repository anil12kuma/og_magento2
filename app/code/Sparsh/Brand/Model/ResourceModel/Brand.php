<?php
/**
 * Class Brand
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Sparsh\Brand\Api\Data\BrandInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DB\Select;

/**
 * Class Brand
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Brand extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TBL_BRAND = 'sparsh_brand';
    const TBL_BRAND_PRODUCTS = 'sparsh_brand_products';
    const TBL_BRAND_STORE = 'sparsh_brand_store';
    const TBL_BRAND_CUSTOMER_GROUP = 'sparsh_brand_customer_group';

    /**
     * DateTime
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

     /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string|null $resourcePrefix resourcePrefix
     */

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->dateTime = $dateTime;
        $this->_storeManager = $storeManager;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sparsh_brand', 'brand_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnection();
    }

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object abstractModel
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime($this->dateTime->gmtDate());
        }

        $object->setUpdateTime($this->dateTime->gmtDate());

        return parent::_beforeSave($object);
    }

    public function load(AbstractModel $object, $value, $field = null)
    {
        $this->entityManager->load($object, $value);
        return $this;
    }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(BrandInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()
            ->from(['cp' => $this->getMainTable()])
            ->join(
                ['cps' => $this->getTable('sparsh_brand_store')],
                'cp.' . $linkField . ' = cps.' . $linkField,
                []
            )
            ->where('cp.brand_id = ?', $identifier)
            ->where('cps.store_id IN (?)', $store);

        if ($isActive !== null) {
            $select->where('cp.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $brandId
     * @return array
     */
    public function lookupStoreIds($brandId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(BrandInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cps' => $this->getTable('sparsh_brand_store')], 'store_id')
            ->join(
                ['cp' => $this->getMainTable()],
                'cps.' . $linkField . ' = cp.' . $linkField,
                []
            )
            ->where('cp.' . $entityMetadata->getIdentifierField() . ' = :brand_id');

        return $connection->fetchCol($select, ['brand_id' => (int)$brandId]);
    }

    /**
     * Get Customer Group ids to which specified item is assigned
     *
     * @param int $brandId
     * @return array
     */
    public function lookupCustomerGroupIds($brandId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(BrandInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cps' => $this->getTable('sparsh_brand_customer_group')], 'customer_group_id')
            ->join(
                ['cp' => $this->getMainTable()],
                'cps.' . $linkField . ' = cp.' . $linkField,
                []
            )
            ->where('cp.' . $entityMetadata->getIdentifierField() . ' = :brand_id');

        return $connection->fetchCol($select, ['brand_id' => (int)$brandId]);
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }

    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {

        $this->entityManager->save($object);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }

}


