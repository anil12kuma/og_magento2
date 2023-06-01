<?php

namespace Amasty\ElasticSearch\Controller\Adminhtml\Stopword;

class NewAction extends AbstractStopWord
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
