<?php

namespace Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\Sorting\Model\Elasticsearch\Adapter\IndexedDataMapper;

class Bestseller extends IndexedDataMapper
{
    public const FIELD_NAME = 'bestsellers';

    /**
     * @inheritdoc
     */
    public function getIndexerCode()
    {
        return 'amasty_sorting_bestseller';
    }
}
