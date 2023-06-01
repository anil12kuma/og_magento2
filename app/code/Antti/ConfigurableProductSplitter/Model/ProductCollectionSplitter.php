<?php
namespace Antti\ConfigurableProductSplitter\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Antti\ConfigurableProductSplitter\Model\ProductDataMerger;
use Magento\Catalog\Model\Layer\Resolver;

class ProductCollectionSplitter
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $catalogLayer;

    /**
     * @var ProductDataMerger
     */
    private $productDataMerger;

    /**
     * ProductCollectionSplitter constructor.
     *
     * @param Resolver $layerResolver
     * @param ProductDataMerger $productDataMerger
     */
    public function __construct(
        Resolver $layerResolver,
        ProductDataMerger $productDataMerger
    ) {
        $this->catalogLayer = $layerResolver->get();
        $this->productDataMerger = $productDataMerger;
    }

    /**
     * @param \Magento\Framework\Data\Collection $collection
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function splitConfigurables(\Magento\Framework\Data\Collection $collection)
    {
        $items = $collection->getItems();

        if (sizeof($items) == 0) {
            return $this;
        }

        $configurables = $otherProducts = [];

        $colorFilterValue = $this->getCurrentColorFilterValue();

        foreach ($items as $index => $product) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection $childProducts */
                $childProducts = $product->getTypeInstance()->getUsedProductCollection($product);
                if ($colorFilterValue !== null) {
                    $childProducts->addAttributeToFilter('color', ['eq' => $colorFilterValue]);
                }
                $childProducts->groupByAttribute('color');

                foreach ($childProducts as $childProduct) {
                    $childProduct->setParentId($product->getId());
                    $otherProducts[] = $childProduct;
                }

                $configurables[$product->getId()] = $product;
            } else {
                $otherProducts[] = $product;
            }

            $collection->removeItemByKey($index);
        }

        foreach ($otherProducts as $product) {
            if ($product->getParentId() && isset($configurables[$product->getParentId()])) {
                $product = $this->productDataMerger->merge($product, $configurables[$product->getParentId()]);
            }
            $collection->addItem($product);
        }

        return $this;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCurrentColorFilterValue()
    {
        /** @var \Magento\Catalog\Model\Layer\Filter\Item $filter */
        foreach ($this->catalogLayer->getState()->getFilters() as $filter) {
            if($filter->getFilter()->getAttributeModel()->getName() == 'color') {
                return $filter->getValueString();
            }
        }

        return null;
    }
}