<?php

namespace Amasty\ElasticSearch\Model\Source;

class KuromojiTokenMode implements \Magento\Framework\Option\ArrayInterface
{
    public const NORMAL = 'normal';
    public const SEARCH = 'search';
    public const EXTENDED = 'extended';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NORMAL, 'label' => __('Normal')],
            ['value' => self::SEARCH, 'label' => __('Search')],
            ['value' => self::EXTENDED, 'label' => __('Extended')]
        ];
    }
}
