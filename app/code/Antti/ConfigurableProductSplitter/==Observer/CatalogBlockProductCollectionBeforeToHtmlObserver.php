<?php
namespace Antti\ConfigurableProductSplitter\Observer;

use Magento\Framework\Event\ObserverInterface;
use Antti\ConfigurableProductSplitter\Model\ProductCollectionSplitter;

class CatalogBlockProductCollectionBeforeToHtmlObserver implements ObserverInterface
{
    /**
     * @var ProductCollectionSplitter
     */
    private $productSplitter;

    /**
     * CatalogBlockProductCollectionBeforeToHtmlObserver constructor.
     *
     * @param ProductCollectionSplitter $productSplitter
     */
    public function __construct(
        ProductCollectionSplitter $productSplitter
    ) {
        $this->productSplitter = $productSplitter;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if ($productCollection instanceof \Magento\Framework\Data\Collection) {
            if (!$productCollection->isLoaded()) {
                $productCollection->load();
            }
            $this->productSplitter->splitConfigurables($productCollection);
        }

        return $this;
    }
}