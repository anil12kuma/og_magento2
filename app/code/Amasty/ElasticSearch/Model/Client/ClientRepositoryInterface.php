<?php

namespace Amasty\ElasticSearch\Model\Client;

/**
 * Interface ClientRepositoryInterface
 */
interface ClientRepositoryInterface
{
    /**
     * @return Elasticsearch
     */
    public function get();
}
