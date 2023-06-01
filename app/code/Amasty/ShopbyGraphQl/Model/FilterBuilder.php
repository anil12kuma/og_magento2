<?php

declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Model;

class FilterBuilder implements FilterBuilderInterface
{
    /**
     * @var array
     */
    private $adapters;

    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }

    public function build(array &$filters, int $storeId): void
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter instanceof FilterBuilderInterface) {
                $adapter->build($filters, $storeId);
            }
        }
    }
}
