<?php

namespace Amasty\ElasticSearch\Model\Indexer\Structure\DynamicTemplate;

class CategoryPosition
{
    /**
     * @param $storeId
     * @return array
     */
    public function map($storeId)
    {
        return [
            'match' => 'category_position_*',
            'match_mapping_type' => 'string',
            'mapping' => [
                'type' => 'integer',
                'index' => false,
            ]
        ];
    }
}
