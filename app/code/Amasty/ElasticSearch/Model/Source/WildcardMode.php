<?php

namespace Amasty\ElasticSearch\Model\Source;

class WildcardMode implements \Magento\Framework\Option\ArrayInterface
{
    public const BOTH = '1';
    public const SUFFIX = '2';
    public const PREFIX = '3';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BOTH,
                'label' => __('*word*'),
            ],
            [
                'value' => self::SUFFIX,
                'label' => __('word*'),
            ],
            [
                'value' => self::PREFIX,
                'label' => __('*word'),
            ]
        ];
    }
}
