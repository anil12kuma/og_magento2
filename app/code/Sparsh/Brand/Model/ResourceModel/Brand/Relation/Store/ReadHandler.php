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
namespace Sparsh\Brand\Model\ResourceModel\Brand\Relation\Store;

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
            $stores = $this->resourceBrand->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
        }
        return $entity;
    }
}
