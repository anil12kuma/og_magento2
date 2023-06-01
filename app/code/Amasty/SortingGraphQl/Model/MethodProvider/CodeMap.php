<?php

declare(strict_types=1);

namespace Amasty\SortingGraphQl\Model\MethodProvider;

class CodeMap
{
    /**
     * @var array
     */
    private $map;

    /**
     * @param string $methodCode
     * @param string $alias
     * @return void
     */
    public function set(string $methodCode, string $alias): void
    {
        $this->map[$alias] = $methodCode;
    }

    /**
     * @param string $alias
     * @return string|null
     */
    public function get(string $alias): ?string
    {
        return $this->map[$alias] ?? null;
    }
}
