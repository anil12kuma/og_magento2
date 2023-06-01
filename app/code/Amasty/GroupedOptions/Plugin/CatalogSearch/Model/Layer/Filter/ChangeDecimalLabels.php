<?php

declare(strict_types=1);

namespace Amasty\GroupedOptions\Plugin\CatalogSearch\Model\Layer\Filter;

use Amasty\GroupedOptions\Model\GroupAttr\DataProvider;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;

class ChangeDecimalLabels
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @param int $attributeId
     * @param FilterItem[] $items
     * @return FilterItem[]
     */
    public function execute(int $attributeId, array $items): array
    {
        $groupRanges = $this->dataProvider->getGroupAttributeRanges($attributeId);

        foreach ($items as $item) {
            $values = $item->getValue();

            if (!is_array($values)) {
                $values = explode('-', (string) $values);
            }

            [$from, $to] = $values;
            $from = (float)$from;
            $to = (float)$to;

            if ($to && $from != $to) {
                /** @see PriceFilter::_renderRangeLabel  */
                $to -= .01;
            }

            foreach ($groupRanges as $groupCode => $groupRange) {
                if (round((float)$groupRange['min'], 2) == round((float)$from, 2)
                    && round((float)$groupRange['max'], 2) == round((float)$to, 2)
                ) {
                    $group = $this->dataProvider->getByCode($groupCode);
                    $item->setLabel($group->getName());
                    unset($groupRanges[$groupCode]);
                }
            }
        }

        return $items;
    }
}
