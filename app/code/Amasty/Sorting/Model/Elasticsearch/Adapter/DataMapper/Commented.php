<?php

namespace Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\Sorting\Model\Elasticsearch\Adapter\IndexedDataMapper;

class Commented extends IndexedDataMapper
{
    public const FIELD_NAME = 'reviews_count';

    /**
     * @inheritdoc
     */
    public function getIndexerCode()
    {
        return 'amasty_yotpo_review';
    }
}
