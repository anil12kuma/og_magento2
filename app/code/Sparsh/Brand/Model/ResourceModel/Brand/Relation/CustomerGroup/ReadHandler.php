<?php
/**
 * Class ReadHandler
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Model\ResourceModel\Brand\Relation\CustomerGroup;

use Sparsh\Brand\Model\ResourceModel\Brand;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Brand
     */
    protected $resourceBrand;

    /**
     * @param MetadataPool $metadataPool
     * @param Brand $resourceBrand
     */
    public function __construct(
        MetadataPool $metadataPool,
        Brand $resourceBrand
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceBrand = $resourceBrand;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $connection = $this->resourceBrand->getConnection();
            $customerGroupIds = $connection
                ->fetchCol(
                    $connection
                        ->select()
                        ->from($this->resourceBrand->getTable('sparsh_brand_customer_group'), ['customer_group_id'])
                        ->where('brand_id = ?', (int)$entity->getId())
                );

            $entity->setData('customer_group_id', $customerGroupIds);
        }
        return $entity;
    }
}
