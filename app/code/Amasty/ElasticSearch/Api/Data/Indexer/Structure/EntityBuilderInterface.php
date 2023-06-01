<?php

namespace Amasty\ElasticSearch\Api\Data\Indexer\Structure;

interface EntityBuilderInterface
{
    /**
     * @return array
     */
    public function buildEntityFields();
}
