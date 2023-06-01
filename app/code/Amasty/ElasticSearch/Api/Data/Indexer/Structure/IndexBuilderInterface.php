<?php

namespace Amasty\ElasticSearch\Api\Data\Indexer\Structure;

interface IndexBuilderInterface
{
    public const MAX_RESULT_COUNT = 1000000;
    public const MAX_FIELDS_COUNT = 1000000;

    /**
     * @return array
     */
    public function build();

    /**
     * @param int $storeId
     * @return \Amasty\ElasticSearch\Api\Data\Indexer\Structure\IndexBuilderInterface;
     */
    public function setStoreId($storeId);
}
