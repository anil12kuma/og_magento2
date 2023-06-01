<?php

namespace Amasty\ElasticSearch\Controller\Adminhtml\Synonym;

use Magento\Framework\Controller\ResultFactory;

class Import extends AbstractSynonym
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(__('Import Synonyms'));

        return $resultPage;
    }
}
