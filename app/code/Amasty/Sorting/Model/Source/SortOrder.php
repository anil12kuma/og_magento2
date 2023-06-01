<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SortOrder implements OptionSourceInterface
{
    public const SORT_ASC = 'asc';
    public const SORT_DESC = 'desc';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::SORT_DESC,
                'label' => __('DESC')
            ],
            [
                'value' => self::SORT_ASC,
                'label' => __('ASC')
            ]
        ];
    }
}
