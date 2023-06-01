<?php

declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Model;

interface FilterBuilderInterface
{
    public function build(array &$filters, int $storeId): void;
}
