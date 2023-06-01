<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    public const DEFAULT_SORTING_SECTION = 'default_sorting';

    public const DEFAULT_SORTING_SEARCH_PAGES_1 = 'search_1';
    public const DEFAULT_SORTING_SEARCH_PAGES_2 = 'search_2';
    public const DEFAULT_SORTING_SEARCH_PAGES_3 = 'search_3';

    public const DEFAULT_SORTING_CATEGORY_PAGES_1 = 'category_1';
    public const DEFAULT_SORTING_CATEGORY_PAGES_2 = 'category_2';
    public const DEFAULT_SORTING_CATEGORY_PAGES_3 = 'category_3';

    public const DISABLED_METHODS_PATH = 'general/disable_methods';

    public const GLOBAL_SORTING_PATH = 'advanced/global';
    public const GLOBAL_SORTING_DIRECTION_PATH = 'advanced/global_direction';

    public const BESTSELLER_ATTRIBUTE_CODE_PATH = 'bestsellers/best_attr';
    public const MOSTVIEWED_ATTRIBUTE_CODE_PATH = 'most_viewed/viewed_attr';
    public const NEW_ATTRIBUTE_CODE_PATH = 'new/new_attr';

    /**
     * @var string
     */
    protected $pathPrefix = 'amsorting/';

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getDefaultSortingSearchPages(?int $storeId = null): array
    {
        $paths = [
            self::DEFAULT_SORTING_SEARCH_PAGES_1,
            self::DEFAULT_SORTING_SEARCH_PAGES_2,
            self::DEFAULT_SORTING_SEARCH_PAGES_3
        ];

        return $this->getDefaultOrders($paths, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getDefaultSortingCategoryPages(?int $storeId = null): array
    {
        $paths = [
            self::DEFAULT_SORTING_CATEGORY_PAGES_1,
            self::DEFAULT_SORTING_CATEGORY_PAGES_2,
            self::DEFAULT_SORTING_CATEGORY_PAGES_3
        ];

        return $this->getDefaultOrders($paths, $storeId);
    }

    private function getDefaultOrders(array $paths, ?int $storeId = null): array
    {
        $defaultOrders = [];
        foreach ($paths as $path) {
            $orderCode = $this->getValue(
                sprintf('%s/%s', self::DEFAULT_SORTING_SECTION, $path),
                $storeId
            );
            if ($orderCode) {
                $defaultOrders[] = $orderCode;
            }
        }

        return $defaultOrders;
    }

    /**
     * @param null|int $storeId
     * @return string[]
     */
    public function getDisabledMethods(?int $storeId = null): array
    {
        $disabledMethods = $this->getValue(self::DISABLED_METHODS_PATH, $storeId);
        if (empty($disabledMethods)) {
            $disabledMethods = [];
        } else {
            $disabledMethods = explode(',', $disabledMethods);
        }

        return $disabledMethods;
    }

    /**
     * @return string|null
     */
    public function getGlobalSorting(): ?string
    {
        return $this->getValue(self::GLOBAL_SORTING_PATH);
    }

    /**
     * @return string
     */
    public function getGlobalSortingDirection(): string
    {
        return (string) $this->getValue(self::GLOBAL_SORTING_DIRECTION_PATH);
    }

    public function getBestsellerAttributeCode(): ?string
    {
        return $this->getValue(self::BESTSELLER_ATTRIBUTE_CODE_PATH);
    }

    public function getMostviewedAttributeCode(): ?string
    {
        return $this->getValue(self::MOSTVIEWED_ATTRIBUTE_CODE_PATH);
    }

    public function getNewAttributeCode(): ?string
    {
        return $this->getValue(self::NEW_ATTRIBUTE_CODE_PATH);
    }
}
