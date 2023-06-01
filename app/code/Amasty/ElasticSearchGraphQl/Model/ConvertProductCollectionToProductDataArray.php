<?php

declare(strict_types=1);

namespace Amasty\ElasticSearchGraphQl\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Review\Model\ReviewFactory;

class ConvertProductCollectionToProductDataArray
{
    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    public function __construct(
        ReviewFactory $reviewFactory
    ) {
        $this->reviewFactory = $reviewFactory;
    }

    public function execute(Collection $productCollection): array
    {
        $result = [];

        foreach ($productCollection as $product) {
            $data = $product->getData();
            $data['model'] = $product;
            $data['is_salable'] = (bool)$product->getIsSalable();
            $data['rating_summary'] = (int)$this->getRating($product);
            $data['reviews_count'] = (int)$product->getRatingSummary()->getReviewsCount();
            $result[$product->getId()] = $data;
        }

        return $result;
    }

    private function getRating(\Magento\Catalog\Model\Product $product): string
    {
        $this->reviewFactory->create()->getEntitySummary($product);

        return $product->getRatingSummary() instanceof \Magento\Framework\DataObject
            ? (string)$product->getRatingSummary()->getRatingSummary()
            : (string)$product->getRatingSummary();
    }
}
