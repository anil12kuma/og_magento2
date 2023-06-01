<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Source;

class SearchSortAfter extends AllSortingAttributes
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = parent::toOptionArray();
        array_shift($options);
        array_unshift($options, [
            'value' => 'relevance',
            'label' => __('Relevance')
        ]);
        array_unshift($options, [
            'value' => '',
            'label' => __('--Please Select--')
        ]);

        return $options;
    }
}
