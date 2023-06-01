<?php

namespace Amasty\ElasticSearch\Controller\Adminhtml\Synonym;

class NewAction extends AbstractSynonym
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
