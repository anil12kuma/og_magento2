<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magestore\Shopbybrand\Controller\Adminhtml\Brand;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class Edit extends Brand
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * edit action
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This brand no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('shopbybrand/*/index');
                return $resultRedirect;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('registry_model', $model);
        /*@var Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_Shopbybrand::brand');
        $resultPage->getConfig()->getTitle()->prepend(__('Brand Manager'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ?
                __('Edit Brand %1', $model->getName()) : __('New Brand')
        );
        return $resultPage;
    }
}
