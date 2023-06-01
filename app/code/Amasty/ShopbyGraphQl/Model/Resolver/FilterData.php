<?php

declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Model\Resolver;

use Amasty\Base\Model\Serializer;
use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface as FilterSettingModel;
use Amasty\ShopbyBase\Model\FilterSetting\FilterResolver;
use Amasty\ShopbyBase\Model\FilterSetting\IsShowProductQuantities;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Amasty\Shopby\Helper\Category;
use Magento\Store\Model\Store;
use Magento\Swatches\Model\Swatch;

class FilterData implements ResolverInterface
{
    public const CATEGORY_ID = 'category_id';

    public const ATTRIBUTES_FILTER = 'attributes_filter';

    public const DISPLAY_MODE_LABEL = 'display_mode_label';

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var DisplayMode
     */
    private $displayMode;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IsShowProductQuantities
     */
    private $isShowProductQuantities;

    /**
     * @var FilterResolver
     */
    private $filterResolver;

    public function __construct(
        ValueFactory $valueFactory,
        Config $eavConfig,
        DisplayMode $displayMode,
        Serializer $serializer,
        ConfigProvider $configProvider,
        Filesystem $filesystem,
        IsShowProductQuantities $isShowProductQuantities,
        FilterResolver $filterResolver
    ) {
        $this->valueFactory = $valueFactory;
        $this->eavConfig = $eavConfig;
        $this->displayMode = $displayMode;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->filesystem = $filesystem;
        $this->isShowProductQuantities = $isShowProductQuantities;
        $this->filterResolver = $filterResolver;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return \Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
            $attributeCode = $value['attribute_code'] == self::CATEGORY_ID
                ? Category::ATTRIBUTE_CODE
                : $value['attribute_code'];
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
            $filterSetting = $this->filterResolver->getFilterSetting($attribute);
            $this->modifyData($filterSetting, $attributeCode, $storeId);
            $data = $filterSetting->getData();
            $data[FilterSettingModel::CATEGORIES_FILTER] = $this->modifyCategoriesFilter($filterSetting);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $data = [];
        }

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }

    private function modifyCategoriesFilter(FilterSettingModel $filterSetting): string
    {
        $categoriesFilter = $filterSetting->getCategoriesFilter();

        return is_string($categoriesFilter) ? $categoriesFilter : implode(',', $categoriesFilter ?? []);
    }

    private function modifyData(FilterSettingModel $filterSetting, string $attributeCode, int $storeId): void
    {
        $this->modifyTooltipData($filterSetting, $storeId);
        $this->setDisplayModeLabel($filterSetting, $attributeCode);
        $this->modifyCustomFilter($filterSetting, $attributeCode);
        $this->getShowProductQuantities($filterSetting);
    }

    private function setDisplayModeLabel(FilterSettingModel $filterSetting, string $attributeCode): void
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
        $this->displayMode->setAttribute($attribute);
        if ($attribute->getFrontendInput() == 'price') {
            $options = $this->displayMode->getAllOptionsDependencies()['price'] ?? [];
        } else {
            $options = $this->displayMode->toOptionArray();
            $options = array_combine(array_column($options, 'value'), array_column($options, 'label'));
        }
        if (!$filterSetting->getDisplayMode()) {
            $filterSetting->setDisplayMode($this->getPreselectDisplayMode($attribute));
        }

        $filterSetting->setData('display_mode_label', $options[(int) $filterSetting->getDisplayMode()]);
    }

    private function getPreselectDisplayMode(AbstractAttribute $attribute): int
    {
        if ($attribute->getFrontendInput() === 'select' && $attribute->getAdditionalData()) {
            $additionalData = $this->serializer->unserialize($attribute->getAdditionalData());
            $frontendInput = $additionalData[Swatch::SWATCH_INPUT_TYPE_KEY] ?? '';
            $preselectValue = DisplayMode::DISPLAY_MODE_FRONTEND_INPUT_MAP[$frontendInput] ?? '';
        }

        return (int) ($preselectValue ?? 0);
    }

    private function modifyTooltipData(FilterSettingModel $filterSetting, int $storeId): void
    {
        $isTooltipsEnabled = $this->configProvider->isTooltipsEnabled();
        $filterSetting->setData('is_tooltips_enabled', $isTooltipsEnabled);
        if ($isTooltipsEnabled) {
            $tooltip = $filterSetting->getTooltip()
                ? $this->serializer->unserialize($filterSetting->getTooltip())
                : '';

            $filterSetting->setTooltip($tooltip[$storeId] ?? $tooltip[Store::DEFAULT_STORE_ID] ?? '');

            $mediaPath = '/' . $this->filesystem->getUri(DirectoryList::MEDIA) . '/';
            $filterSetting->setData('tooltips_image', $mediaPath . $this->configProvider->getTooltipSrc());
        }
    }

    private function modifyCustomFilter(FilterSettingModel $filterSetting, string $attributeCode): void
    {
        switch ($attributeCode) {
            case 'rating_summary':
                $config = $this->configProvider->getRatingConfig();
                $config[self::ATTRIBUTES_FILTER] = 'rating';
                $config[self::DISPLAY_MODE_LABEL] = 'rating';
                break;
            case 'stock_status':
                $config = $this->configProvider->getStockConfig();
                $config[self::ATTRIBUTES_FILTER] = 'stock';
                $config[self::DISPLAY_MODE_LABEL] = 'stock';
                break;
            case 'am_is_new':
                $config = $this->configProvider->getNewConfig();
                $config[self::ATTRIBUTES_FILTER] = 'am_is_new';
                $config[self::DISPLAY_MODE_LABEL] = 'am_is_new';
                break;
            case 'am_on_sale':
                $config = $this->configProvider->getOnSaleConfig();
                $config[self::ATTRIBUTES_FILTER] = 'am_on_sale';
                $config[self::DISPLAY_MODE_LABEL] = 'am_on_sale';
                break;
            case 'category_ids':
                $config[self::DISPLAY_MODE_LABEL] = 'category_ids';
                break;
        }
        if (isset($config)) {
            $filterSetting->setData(array_merge($filterSetting->getData(), $config));
        }
    }

    private function getShowProductQuantities(FilterSettingModel $filterSetting): void
    {
        $isShowProductQuantities = (int) $filterSetting->getShowProductQuantities();
        $isShowProductQuantities = $this->isShowProductQuantities->execute($isShowProductQuantities);
        $filterSetting->setShowProductQuantities($isShowProductQuantities);
    }
}
