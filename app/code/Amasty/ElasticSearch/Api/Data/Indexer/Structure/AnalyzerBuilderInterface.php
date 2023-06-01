<?php

namespace Amasty\ElasticSearch\Api\Data\Indexer\Structure;

interface AnalyzerBuilderInterface
{
    /**
     * @param int $storeId
     * @return array
     */
    public function build($storeId);
}
