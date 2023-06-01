<?php

namespace Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule;

class NewAction extends AbstractRelevance
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
