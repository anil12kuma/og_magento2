<?php

namespace Amasty\SortingGraphQl\Plugin\Sorting\Plugin\Catalog;

use Amasty\Sorting\Plugin\Catalog\Config;

class ConfigPlugin
{
    /**
     * @param Config $subject
     * @param array $options
     * @return array
     */
    public function afterAfterGetAttributesUsedForSortBy(Config $subject, array $options)
    {
        foreach ($options as $key => $option) {
            $options[$key] = [
                'attribute_code' => $option->getData('attribute_code'),
                'frontend_label' => $option->getStoreLabel()
            ];
        }

        return $options;
    }
}
