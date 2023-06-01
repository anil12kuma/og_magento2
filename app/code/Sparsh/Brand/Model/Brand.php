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
namespace Sparsh\Brand\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Sparsh\Brand\Api\Data\BrandInterface ;

/**
 * Class Brand
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Brand extends AbstractModel implements BrandInterface, IdentityInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const CACHE_TAG = 'sparsh_brand_grid';

    const TBL_BRAND = 'sparsh_brand';
    const TBL_BRAND_PRODUCTS = 'sparsh_brand_products';
    const TBL_BRAND_STORE = 'sparsh_brand_store';
    const TBL_BRAND_CUSTOMER_GROUP = 'sparsh_brand_customer_group';

    /**
     * Cache Tag
     *
     * @var string
     */
    protected $_cacheTag = 'sparsh_brand_grid';

    /**
     * EventPrefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sparsh_brand_grid';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Sparsh\Brand\Model\ResourceModel\Brand::class);
    }

    /**
     * {@inheritdoc}
     *
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get products of current Brand
     *
     * @param Brand $object object
     *
     * @return array
     */
    public function getProducts(\Sparsh\Brand\Model\Brand $object)
    {
        $tbl = $this->getResource()->getTable(
            \Sparsh\Brand\Model\ResourceModel\Brand::TBL_BRAND_PRODUCTS
        );
        $select = $this->getResource()->getConnection()->select()
        ->from(
            $tbl,
            ['product_id']
        )->where(
            'brand_id = ?',
            (int)$object->getId()
        );
        return $this->getResource()->getConnection()->fetchCol($select);
    }

    /**
     * Get Available Status for enable/disable
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Receive brand store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : (array)$this->getData('store_id');
    }

    /**
     * @return array
     */
    public function getCustomerGroup()
    {
        return  (array)$this->getData('customer_group_id');
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId(){
        return parent::getData(self::BRAND_ID);
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle(){
        return parent::getData(self::TITLE);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(){
        return parent::getData(self::DESCRIPTION);
    }

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle(){
        return parent::getData(self::META_TITLE);
    }

    /**
     * Get meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords(){
        return parent::getData(self::META_KEYWORDS);
    }

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription(){
        return parent::getData(self::META_DESCRIPTION);
    }

    /**
     * Get url key
     *
     * @return string|null
     */
    public function getUrlKey(){
        return parent::getData(self::URL_KEY);
    }

    /**
     * Get Position
     *
     * @return string|null
     */
    public function getPosition(){
        return parent::getData(self::POSITION);
    }

    /**
     * Get image
     *
     * @return string|null
     */
    public function getImage(){
        return parent::getData(self::IMAGE);
    }

    /**
     * Get banner image
     *
     * @return string|null
     */
    public function getBannerImage(){
        return parent::getData(self::BANNER_IMAGE);
    }

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus(){
        return parent::getData(self::STATUS);
    }

    /**
     * Set Id
     *
     * @param int $id id
     *
     * @return mixed
     */
    public function setId($id){
        return $this->setData(self::BRAND_ID, $id);
    }

    /**
     * Set Title
     *
     * @param string $title title
     *
     * @return mixed
     */
    public function setTitle($title){
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set Description
     *
     * @param string $description description
     *
     * @return mixed
     */
    public function setDescription($description){
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set meta title
     *
     * @param string $metaTitle metaTitle
     *
     * @return mixed
     */
    public function setMetaTitle($metaTitle){
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords metaKeywords
     *
     * @return mixed
     */
    public function setMetaKeywords($metaKeywords){
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription metaDescription
     *
     * @return mixed
     */
    public function setMetaDescription($metaDescription){
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Set Url key
     *
     * @param string $urlKey urlKey
     *
     * @return mixed
     */
    public function setUrlKey($urlKey){
        return $this->setData(self::URL_KEY, $id);
    }

    /**
     * Set IsActive
     *
     * @param isActive $isActive isActive
     *
     * @return mixed
     */
    public function setIsActive($isActive){
        return $this->setData(self::STATUS, $isActive);
    }
}
