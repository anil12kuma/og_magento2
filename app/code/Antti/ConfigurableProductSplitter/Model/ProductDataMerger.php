<?php
namespace Antti\ConfigurableProductSplitter\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\EventManager;

class ProductDataMerger
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @param EventManager $eventManager
     */
    public function __construct(
        EventManager $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * @param ProductInterface $product
     * @param ProductInterface $parentProduct
     *
     * @return ProductInterface
     */
    public function merge(ProductInterface $product, ProductInterface $parentProduct)
    {
        $merged = clone $parentProduct;
        $merged->setParentId($merged->getId());
        $merged->setId($product->getId());

        $this->setImageFromChildProduct($merged, $product);

        $this->eventManager->dispatch(
            'cps_product_data_merge_after',
            ['merged' => $merged, 'simple' => $product, 'configurable' => $parentProduct]
        );

        return $merged;
    }

    /**
     * @param ProductInterface $product
     * @param ProductInterface $childProduct
     */
    public function setImageFromChildProduct(ProductInterface &$product, ProductInterface $childProduct)
    {
        foreach (['image', 'small_image', 'thumbnail'] as $imageType) {
            if ($childProduct->getData($imageType) && $childProduct->getData($imageType) !== 'no_selection') {
                $product->setData($imageType, $childProduct->getData($imageType));
            } else {
                $product->setData($imageType, $childProduct->getData('image'));
            }
        }
    }
}