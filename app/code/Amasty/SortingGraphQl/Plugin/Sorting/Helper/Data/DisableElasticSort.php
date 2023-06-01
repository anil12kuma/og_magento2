<?php

declare(strict_types=1);

namespace Amasty\SortingGraphQl\Plugin\Sorting\Helper\Data;

use Amasty\Sorting\Helper\Data;

class DisableElasticSort
{
    /**
     * @param Data $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundIsElasticSort(Data $subject, callable $proceed): bool
    {
        return false;
    }
}
