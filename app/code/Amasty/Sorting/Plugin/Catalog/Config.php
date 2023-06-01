<?php

namespace Amasty\Sorting\Plugin\Catalog;

use Amasty\Sorting\Helper\Data;
use Amasty\Sorting\Model\Method\IsMethodDisabledByConfig;
use Amasty\Sorting\Model\MethodProvider;
use Amasty\Sorting\Model\Plugin\Catalog\Config\DisplayAllFlag;
use Amasty\Sorting\Model\SortingAdapterFactory;
use Magento\Catalog\Model\Config as CatalogEavConfig;
use Magento\Framework\View\LayoutInterface;

/**
 * Plugin Config
 * plugin name: AddSortingMethods
 * type: \Magento\Catalog\Model\Config
 */
class Config
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var MethodProvider
     */
    private $methodProvider;

    /**
     * @var SortingAdapterFactory
     */
    private $adapterFactory;

    /**
     * @var array
     */
    private $correctSortOrder;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var DisplayAllFlag
     */
    private $displayAllFlag;

    /**
     * @var IsMethodDisabledByConfig
     */
    private $isMethodDisabledByConfig;

    public function __construct(
        Data $helper,
        MethodProvider $methodProvider,
        SortingAdapterFactory $adapterFactory,
        LayoutInterface $layout,
        DisplayAllFlag $displayAllFlag,
        IsMethodDisabledByConfig $isMethodDisabledByConfig
    ) {
        $this->helper = $helper;
        $this->methodProvider = $methodProvider;
        $this->adapterFactory = $adapterFactory;
        $this->correctSortOrder = array_keys($this->helper->getSortOrder());
        $this->layout = $layout;
        $this->displayAllFlag = $displayAllFlag;
        $this->isMethodDisabledByConfig = $isMethodDisabledByConfig;
    }

    /**
     * Retrieve Attributes array used for sort by
     *
     * @param CatalogEavConfig $subject
     * @param array $options
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributesUsedForSortBy(CatalogEavConfig $subject, array $options): array
    {
        foreach ($options as $key => $option) {
            if (!$this->isMethodDisplayed($key)) {
                unset($options[$key]);
            }
        }

        return $this->addNewOptions($options);
    }

    /**
     * @param array $options
     * @return array
     */
    public function addNewOptions(array $options): array
    {
        $methods = $this->methodProvider->getMethods();

        foreach ($methods as $methodObject) {
            $code = $methodObject->getMethodCode();
            if ($this->isMethodDisplayed($code) && !isset($options[$code])) {
                $options[$code] = $this->adapterFactory->create(['methodModel' => $methodObject]);
            }
        }

        return $options;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @param CatalogEavConfig $subject
     * @param array $options
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributeUsedForSortByArray(CatalogEavConfig $subject, array $options): array
    {
        if (!$this->isMethodDisplayed('position')) {
            unset($options['position']);
        }

        $options = $this->sortOptions($options);

        if (count($options) == 0 && !$this->layout->getBlock('search.result')) {
            $options[] = '';
        }

        return $options;
    }

    /**
     * @param array $options
     * @return array $sortedOptions
     */
    private function sortOptions($options = [])
    {
        uksort($options, [$this, "sortingRule"]);

        return $options;
    }

    private function sortingRule($first, $second)
    {
        $firstValue = array_search($first, $this->correctSortOrder);
        $secondValue = array_search($second, $this->correctSortOrder);
        if ($firstValue < $secondValue) {
            return -1;
        } elseif ($firstValue == $secondValue) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * @param string $methodCode
     * @return bool
     */
    private function isMethodDisplayed(string $methodCode): bool
    {
        return $this->displayAllFlag->get() || !$this->isMethodDisabledByConfig->execute($methodCode);
    }
}
