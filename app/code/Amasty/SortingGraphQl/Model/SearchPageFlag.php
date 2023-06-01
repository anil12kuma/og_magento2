<?php

declare(strict_types=1);

namespace Amasty\SortingGraphQl\Model;

class SearchPageFlag
{
    /**
     * @var bool
     */
    private $flag = false;

    public function set(bool $flag): void
    {
        $this->flag = $flag;
    }

    public function get(): bool
    {
        return $this->flag;
    }
}
