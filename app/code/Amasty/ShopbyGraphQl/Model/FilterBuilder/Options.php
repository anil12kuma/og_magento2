<?php

declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Model\FilterBuilder;

use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Model\Source\SortOptionsBy;
use Amasty\ShopbyBase\Helper\OptionSetting as OptionSettingHelper;
use Amasty\ShopbyBase\Model\FilterSettingRepository;
use Amasty\ShopbyGraphQl\Model\FilterBuilderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Swatches\Helper\Data;
use Psr\Log\LoggerInterface;

class Options implements FilterBuilderInterface
{
    public const VALUE = 'value';

    /**
     * @var OptionSettingHelper
     */
    private $optionSettingHelper;

    /**
     * @var FilterSettingRepository
     */
    private $filterSettingRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $swatchesData;

    public function __construct(
        OptionSettingHelper $optionSettingHelper,
        FilterSettingRepository $filterSettingRepository,
        Data $swatchesData,
        LoggerInterface $logger
    ) {
        $this->optionSettingHelper = $optionSettingHelper;
        $this->filterSettingRepository = $filterSettingRepository;
        $this->logger = $logger;
        $this->swatchesData = $swatchesData;
    }

    public function build(array &$filters, int $storeId): void
    {
        foreach ($filters as $key => &$filter) {
            foreach ($filter['options'] ?? [] as $optionKey => $option) {
                $filter['options'][$optionKey] += $this->getOptionData($option, $filter, $storeId);
            }
            $this->sortFilterOptions($filter);
        }
    }

    private function getOptionData(array $option, array $filter, int $storeId): array
    {
        try {
            $value = $option[self::VALUE];
            $optionSetting = $this->optionSettingHelper->getSettingByValue(
                $value,
                FilterSetting::ATTR_PREFIX . $filter['attribute_code'],
                $storeId
            );
            $data = $optionSetting->getData();
            $data['image'] = $this->swatchesData->getSwatchesByOptionsId([$value])[$value][self::VALUE] ?? '';
            $data['position'] = $optionSetting->getAttributeOption()->getSortOrder();
        } catch (\Exception $e) {
            $data = [];
            $this->logger->error($e->getMessage());
        }

        return $data;
    }

    private function sortFilterOptions(array &$filter): void
    {
        if (isset($filter['options']) && !empty($filter['options'])) {
            $attributeCode = str_replace('_bucket', '', $filter['attribute_code']);
            try {
                $filterSetting = $this->filterSettingRepository->getFilterSetting($attributeCode);
            } catch (NoSuchEntityException $e) {
                $filterSetting = null;
            }
            if ($filterSetting && $filterSetting->getSortOptionsBy() == SortOptionsBy::NAME) {
                usort($filter['options'], function ($first, $second) {
                    return strcmp($first['label'], $second['label']);
                });
            }
        }
    }
}
