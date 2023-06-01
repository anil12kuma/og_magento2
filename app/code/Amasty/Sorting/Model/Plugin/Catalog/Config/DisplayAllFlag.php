<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Plugin\Catalog\Config;

/**
 * @see \Amasty\Sorting\Plugin\Catalog\Config
 * When flag set as true - all options must be displayed by plugin
 */
class DisplayAllFlag
{
    /**
     * @var bool
     */
    private $flag = false;

    /**
     * @param bool $flag
     * @return void
     */
    public function set(bool $flag): void
    {
        $this->flag = $flag;
    }

    /**
     * @return bool
     */
    public function get(): bool
    {
        return $this->flag;
    }
}
