<?php
namespace Sparsh\Brand\Model;

use Sparsh\Brand\Api\BrandRepositoryInterface;
use Sparsh\Brand\Api\Data\BrandInterface;
use Sparsh\Brand\Model\BrandFactory;
use Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchResultsInterfaceFactory;

class BrandRepository implements BrandRepositoryInterface
{
    protected $objectFactory;
    protected $dataBrandFactory;
    protected $dataObjectHelper;
    protected $dataObjectProcessor;
    protected $collectionFactory;
    public function __construct(
        BrandFactory $objectFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        \Sparsh\Brand\Api\Data\BrandInterfaceFactory $dataBrandFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    )
    {
        $this->objectFactory        = $objectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBrandFactory = $dataBrandFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionFactory    = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function save(BrandInterface $brand)
    {
        if (empty($brand->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $brand->setStoreId($storeId);
        }
        try {
            $this->resource->save($brand);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__(
                'Could not save the brand: %1',
                $e->getMessage()
            ));
        }
        return $brand;
    }

    public function getById($id)
    {
        $brand = $this->objectFactory->create();
        $brand->load($id);

        if (!$brand->getId()) {
            throw new NoSuchEntityException(__('Brand with id "%1" does not exist.', $id));
        }
        return $brand;
    }

    public function delete(BrandInterface $brand){
        try {
            $brand->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
        return true;
    }

    public function deleteById($id){
        return $this->delete($this->getById($id));
    }
}
