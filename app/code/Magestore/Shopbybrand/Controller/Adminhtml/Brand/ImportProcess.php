<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magento\Framework\Controller\ResultFactory;

/**
 * Action Edit
 */
class ImportProcess extends \Magestore\Shopbybrand\Controller\Adminhtml\Brand
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);;
        $fileData = $this->getRequest()->getFiles();
        if (isset($fileData['csv_brand']['tmp_name'])
            && !empty($fileData['csv_brand']['tmp_name'])) {
            try {

                /** @var \Magestore\Shopbybrand\Model\ResourceModel\Brand $brandResource */
                $brandResource = $this->_objectManager->get('Magestore\Shopbybrand\Model\ResourceModel\Brand');
                $number = $brandResource->import(
                    $this->getRequest()->getParam('is_update'),
                    $fileData['csv_brand']['tmp_name']
                );

                $this->messageManager->addSuccess(
                    __('You\'ve successfully imported ') .
                    $number['insert'] . __(' new item(s) and updated ') .
                    $number['update'] . ' ' . __('item(s). Please reindex brand URL key.')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invalid file upload attempt'));

                return $resultRedirect->setPath('*/*/importbrand');
            }
        } else {
            $this->messageManager->addError(__('Invalid file upload attempt'));

            return $resultRedirect->setPath('*/*/importbrand');
        }


        return $resultRedirect->setPath('*/*/index');
    }
}
