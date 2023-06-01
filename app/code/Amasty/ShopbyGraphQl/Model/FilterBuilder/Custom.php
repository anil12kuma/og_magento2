<?php

declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Model\FilterBuilder;

use Amasty\ShopbyGraphQl\Model\FilterBuilderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Custom implements FilterBuilderInterface
{
    public const RATING_CONFIG_PATH = 'amshopby/rating_filter';
    public const IS_NEW_CONFIG_PATH = 'amshopby/am_is_new_filter';
    public const ON_SALE_CONFIG_PATH = 'amshopby/am_on_sale_filter';
    public const STOCK_CONFIG_PATH = 'amshopby/stock_filter';

    public const RATING_SUMMARY_BUCKET = 'rating_summary_bucket';
    public const STOCK_STATUS_BUCKET = 'stock_status_bucket';
    public const IS_NEW_BUCKET = 'am_is_new_bucket';
    public const ON_SALE_BUCKET = 'am_on_sale_bucket';
    public const RATING_STARS = 5;

    public const VALUE = 'value';

    public const INVALID_OPTION = 0;

    /**
     * @var array
     */
    private $bucketNameFilter = [
        self::RATING_SUMMARY_BUCKET,
        self::STOCK_STATUS_BUCKET,
        self::IS_NEW_BUCKET,
        self::ON_SALE_BUCKET
    ];

    /**
     * @var array
     */
    private $fixOptionsFilter = [
        self::IS_NEW_BUCKET,
        self::ON_SALE_BUCKET
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function build(array &$filters, int $storeId): void
    {
        foreach ($filters as $key => &$filter) {
            if (in_array($key, $this->bucketNameFilter)) {
                $filter['attribute_code'] = str_replace('_bucket', '', $filter['attribute_code']);
                $configPath = '';
                switch ($key) {
                    case self::RATING_SUMMARY_BUCKET:
                        $configPath = self::RATING_CONFIG_PATH;
                        $this->prepareRatingFilter($filter);
                        break;
                    case self::IS_NEW_BUCKET:
                        $configPath = self::IS_NEW_CONFIG_PATH;
                        break;
                    case self::ON_SALE_BUCKET:
                        $configPath = self::ON_SALE_CONFIG_PATH;
                        break;
                    case self::STOCK_STATUS_BUCKET:
                        $configPath = self::STOCK_CONFIG_PATH;
                        break;
                }

                if ($this->isFilterEnabled($configPath)) {
                    $filter['label'] = $this->getFilterLabel($configPath);
                    if (in_array($key, $this->fixOptionsFilter)) {
                        if (!$this->fixCustomOptions($filter)) {
                            unset($filters[$key]);
                        }
                    }
                } else {
                    unset($filters[$key]);
                }
            }
        }
    }

    private function getFilterLabel(string $configPath): string
    {
        return (string) $this->scopeConfig->getValue(
            $configPath . '/label',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function isFilterEnabled(string $configPath): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            $configPath . '/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function prepareRatingFilter(array &$ratingFilter): void
    {
        $allCount = 0;
        $listData = [];

        for ($key = self::RATING_STARS; $key >= 1; $key--) {
            foreach ($ratingFilter['options'] as $option) {
                if ($key == $this->calculateRatingValue($option)) {
                    $allCount += $option['count'];

                    $listData[$key] = [
                        'label' => $key * 20,
                        self::VALUE => $key,
                        'count' => $allCount
                    ];
                }
            }
        }

        if ($listData) {
            $ratingFilter['options'] = array_values($listData);
        }
    }

    private function calculateRatingValue(array $option): int
    {
        return (int) ($option[self::VALUE] / 20);
    }

    private function fixCustomOptions(array &$filter): bool
    {
        if (isset($filter['options'][self::INVALID_OPTION])) {
            unset($filter['options'][self::INVALID_OPTION]);
        }

        return count($filter['options']) > 0;
    }
}
