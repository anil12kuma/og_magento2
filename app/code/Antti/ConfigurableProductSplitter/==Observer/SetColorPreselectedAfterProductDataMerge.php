<?php
namespace Antti\ConfigurableProductSplitter\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Config as EavConfig;

class SetColorPreselectedAfterProductDataMerge implements ObserverInterface
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * ProductDataMerger constructor.
     *
     * @param EavConfig $eavConfig
     */
    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getSimple();
        $merged = $observer->getEvent()->getMerged();

        $this->setColorPreselected($merged, $product->getColor());

        return $this;
    }

    /**
     * @param ProductInterface $product
     * @param int $color
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setColorPreselected(ProductInterface &$product, int $color)
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'color');

        $preconfiguredValues = new \Magento\Framework\DataObject();
        $preconfiguredValues->setData('super_attribute', [$attribute->getId() => $color]);
        $product->setPreconfiguredValues($preconfiguredValues);

        // TODO: should test whether this works if there is no url rewrite
        $product->setRequestPath(sprintf('%s#%d=%d', $product->getRequestPath(), $attribute->getId(), $color));
    }
}