<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Shopbybrand\Model\Indexer\Brand;

use Magestore\Shopbybrand\Model\Indexer\AbstractIndexer;

class BrandProductIndexer extends AbstractIndexer
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexFull();
        $this->getCacheContext()->registerTags($this->getIdentities());
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexFull();
    }
}
