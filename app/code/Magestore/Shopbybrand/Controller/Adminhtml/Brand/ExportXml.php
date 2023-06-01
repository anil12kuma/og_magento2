<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magestore\Shopbybrand\Controller\Adminhtml\Brand;

/**
 * Action ExportXml
 */
class ExportXml extends Brand
{
    /**
     * Execute action
     */
    public function execute()
    {
        $fileName = 'Brands.xml';

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $content = $resultPage->getLayout()
            ->createBlock('Magestore\Shopbybrand\Block\Adminhtml\Brand\Export')->getXml();

        /** @var \Magento\Framework\App\Response\Http\FileFactory $fileFactory */
        $fileFactory = $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory');

        return $fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
