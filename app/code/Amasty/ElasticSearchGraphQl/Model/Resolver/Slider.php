<?php

declare(strict_types=1);

namespace Amasty\ElasticSearchGraphQl\Model\Resolver;

use Amasty\ElasticSearchGraphQl\Model\ConvertProductCollectionToProductDataArray;
use Amasty\Xsearch\Model\Slider\RecentlyViewed\ProductsProvider;
use Amasty\Xsearch\Model\Slider\SliderProductsProviderInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Slider implements ResolverInterface
{
    /**
     * @var ProductsProvider
     */
    private $productsProvider;

    /**
     * @var Config
     */
    private $catalogAttributeConfig;

    /**
     * @var State
     */
    private $state;

    /**
     * @var ConvertProductCollectionToProductDataArray
     */
    private $convertProductModelToProductDataArray;

    public function __construct(
        SliderProductsProviderInterface $productsProvider,
        Config $catalogAttributeConfig,
        State $state,
        ConvertProductCollectionToProductDataArray $convertProductModelToProductDataArray
    ) {
        $this->productsProvider = $productsProvider;
        $this->catalogAttributeConfig = $catalogAttributeConfig;
        $this->state = $state;
        $this->convertProductModelToProductDataArray = $convertProductModelToProductDataArray;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $productItems = $this->getProductItems();
        $this->state->emulateAreaCode(Area::AREA_FRONTEND, [$this, 'getProductItems']);

        return [
            'items' => $productItems,
            'total_count' => count($productItems),
            'code' => 'product'
        ];
    }

    /**
     * Return array of product data arrays
     *
     * @return array[]
     */
    public function getProductItems(): array
    {
        /** @var ProductCollection $productCollection * */
        $productCollection = $this->productsProvider->getProducts();
        $productCollection->addAttributeToSelect($this->catalogAttributeConfig->getProductAttributes());

        return $this->convertProductModelToProductDataArray->execute($productCollection);
    }
}
