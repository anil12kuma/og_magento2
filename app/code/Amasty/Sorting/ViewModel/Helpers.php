<?php

declare(strict_types=1);

namespace Amasty\Sorting\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Output;
use Magento\Framework\Data\Helper\PostHelper;

class Helpers
{
    /**
     * @var Output
     */
    private $outputHelper;

    /**
     * @var PostHelper
     */
    private $postHelper;

    public function __construct(
        Output $outputHelper,
        PostHelper $postHelper
    ) {
        $this->outputHelper = $outputHelper;
        $this->postHelper = $postHelper;
    }

    /**
     * @param ProductInterface $product
     * @param string $attributeHtml
     * @param string $attributeName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductAttribute(
        ProductInterface $product,
        string $attributeHtml,
        string $attributeName
    ): string {
        return $this->outputHelper->productAttribute($product, $attributeHtml, $attributeName);
    }

    /**
     * @param string $addToCartUrl
     * @param int $entityId
     * @return string
     */
    public function getPostData(string $addToCartUrl, int $entityId): string
    {
        return $this->postHelper->getPostData($addToCartUrl, ['product' => $entityId]);
    }
}
