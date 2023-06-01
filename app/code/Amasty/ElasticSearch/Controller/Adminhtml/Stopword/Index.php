<?php

namespace Amasty\ElasticSearch\Controller\Adminhtml\Stopword;

use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractStopWord
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(__('Manage Stop Words'));

        return $resultPage;
    }
}
