<?php

namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery;

use Magento\Framework\Search\Request\QueryInterface;

interface InjectSubqueryInterface
{
    /**
     * @param QueryInterface $request
     * @param array $elasticQuery
     * @param string $conditionType
     * @return array
     */
    public function execute(array $elasticQuery, QueryInterface $request, $conditionType);
}
