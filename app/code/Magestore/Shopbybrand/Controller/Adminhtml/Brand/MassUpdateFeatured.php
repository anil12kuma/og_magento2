<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magestore\Shopbybrand\Controller\Adminhtml\Brand;
use Magestore\Shopbybrand\Model\BrandFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

class MassUpdateFeatured extends Brand
{
    /**
     * @var \Magestore\Shopbybrand\Model\BrandFactory
     */
    protected $brandFactory;

    /**
     * @param Context $context
     * @param BrandFactory $brandFactory
     */
    public function __construct(
        Context $context,
        BrandFactory $brandFactory
    ) {
        parent::__construct($context);
        $this->brandFactory = $brandFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select brand(s).'));
        } else {
            try {
                $status = $this->getRequest()->getParam('is_featured');
                foreach ($ids as $id) {
                    $model = $this->brandFactory->create()->load($id);
                    $model->setIsFeatured($status)->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 brand(s) have been updated.', count($ids))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while updating these brand(s).')
                );
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('shopbybrand/*/' . $this->getRequest()->getParam('ret', 'index'));
        return $resultRedirect;
    }
}
