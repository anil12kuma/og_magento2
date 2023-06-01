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
namespace Sparsh\Brand\Model\ResourceModel\BrandProduct;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

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
    protected $_idFieldName = 'brand_product_id';

    protected $customerSesion;

    public function __construct(
         \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Customer\Model\SessionFactory $customerSesion
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
       $this->customerSesion = $customerSesion;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Sparsh\Brand\Model\BrandProduct::class,
            \Sparsh\Brand\Model\ResourceModel\BrandProduct::class
        );
    }

    /**
     * Return Products of given brandId
     *
     * @param int $brandId brandId
     *
     * @return void
     */
    public function filterBrandProducts($brandId)
    {
        $this->sparshBrandTable = $this->getTable(\Sparsh\Brand\Model\ResourceModel\Brand::TBL_BRAND);

        $this->getSelect()
            ->join(
                ['brand' => $this->sparshBrandTable],
                'main_table.brand_id= brand.brand_id and brand.status='.\Sparsh\Brand\Model\Brand::STATUS_ENABLED
            );

        $this->getSelect()
            ->where("brand.brand_id=".$brandId);
    }

    /**
     * Return Brands of given productId
     *
     * @param int $productId productId
     *
     * @return void
     */
    public function filterBrands($productId,$storeId)
    {
        $this->sparshBrandTable = $this->getTable(\Sparsh\Brand\Model\ResourceModel\Brand::TBL_BRAND);
        $this->sparshBrandProductsTable = $this->getTable(\Sparsh\Brand\Model\ResourceModel\Brand::TBL_BRAND_PRODUCTS);
        $this->sparshBrandStoreTable = $this->getTable(\Sparsh\Brand\Model\ResourceModel\Brand::TBL_BRAND_STORE);
        $this->sparshBrandCustomerTable = $this->getTable(\Sparsh\Brand\Model\ResourceModel\Brand::TBL_BRAND_CUSTOMER_GROUP);

        $currentCustomerGroup = $this->getCustomerGroup();

        $this->getSelect()
            ->join(
                ['brand' => $this->sparshBrandTable],
                'main_table.brand_id= brand.brand_id and brand.status='.\Sparsh\Brand\Model\Brand::STATUS_ENABLED
            );

        $this->getSelect()
            ->join(
                ['brandStore' => $this->sparshBrandStoreTable],
                'main_table.brand_id = brandStore.brand_id '
            );

        $this->getSelect()
            ->join(
                ['brandCustomer' => $this->sparshBrandCustomerTable],
                'main_table.brand_id = brandCustomer.brand_id '
            );

        $this->getSelect()
            ->where("main_table.product_id=".$productId." AND (brandStore.store_id =".$storeId." OR brandStore.store_id = 0 ) AND brandCustomer.customer_group_id = ".$currentCustomerGroup);


    }

    public function getCustomerGroup()
    {
        if ($this->customerSesion->create()->isLoggedIn()) {
            return $this->customerSesion->create()->getCustomer()->getGroupId();

        }
        return 0;
    }

}

