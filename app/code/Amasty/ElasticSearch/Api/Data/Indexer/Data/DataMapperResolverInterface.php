<?php

namespace Amasty\ElasticSearch\Api\Data\Indexer\Data;

interface DataMapperResolverInterface
{
    /**
     * @param int $entityId
     * @param array $indexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function mapEntityData(array $indexData, $storeId, $context = []);
}
