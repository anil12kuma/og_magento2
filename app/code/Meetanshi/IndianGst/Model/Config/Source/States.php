<?php
namespace Meetanshi\IndianGst\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

class States implements Arrayinterface
{
    protected $regionCollectionFactory;
    public function __construct(
        CollectionFactory $regionCollectionFactory
    ) {
    
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    public function toOptionArray()
    {
        $colllection = $this->regionCollectionFactory->create()->addFieldToFilter('country_id', 'IN');
        foreach ($colllection as $region) {
            $result[] = ['value' => $region->getRegionId(), 'label'=>__($region->getName())];
        }
        return $result;
    }
}
