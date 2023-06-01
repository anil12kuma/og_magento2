<?php
/**
 * Class SaveHandler
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

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Sparsh\Brand\Api\Data\BrandInterface;
use Sparsh\Brand\Model\ResourceModel\Brand;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
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
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {   
        $entityMetadata = $this->metadataPool->getMetadata(BrandInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldCustomer = $this->resourceBrand->lookupCustomerGroupIds((int)$entity->getId());
        
        $newCustomer = (array)$entity->getCustomerGroup();
        
        if (empty($newCustomer)) {
            $newCustomer = (array)$entity->getCustomerGroupId();
        }

        $table = $this->resourceBrand->getTable('sparsh_brand_customer_group');

        $delete = array_diff($oldCustomer, $newCustomer);
       
        if ($delete) {
            
            $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'customer_group_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }
        
        $insert = array_diff($newCustomer, $oldCustomer);
        
        if ($insert) {
            
            $data = [];
            foreach ($insert as $customerGroupId) {
                $data[] = [
                    $linkField => (int)$entity->getData($linkField),
                    'customer_group_id' => (int)$customerGroupId
                ];
            }
            
            $connection->insertMultiple($table, $data);
        }
        
        return $entity;
    }
}
