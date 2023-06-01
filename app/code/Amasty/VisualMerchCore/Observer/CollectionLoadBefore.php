<?php

declare(strict_types=1);

namespace Amasty\VisualMerchCore\Observer;

use Amasty\VisualMerchCore\Model\ResourceModel\Product\Collection\AddVisibilityFilter;

class CollectionLoadBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var AddVisibilityFilter
     */
    private $addVisibilityFilter;

    public function __construct(AddVisibilityFilter $addVisibilityFilter)
    {
        $this->addVisibilityFilter = $addVisibilityFilter;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $collection = $observer->getEvent()->getDataObject();
        $this->addVisibilityFilter->execute($collection);
    }
}
