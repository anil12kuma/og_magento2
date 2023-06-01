<?php

namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery\ServicePreprocessor;

interface PreprocessorInterface
{
    /**
     * @param string $query
     * @return string
     */
    public function process($query);
}
