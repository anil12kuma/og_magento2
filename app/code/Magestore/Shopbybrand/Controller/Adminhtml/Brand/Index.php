<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magestore\Shopbybrand\Controller\Adminhtml\Brand;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Brand
{
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
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_Shopbybrand::brand');
        $resultPage->addBreadcrumb(__('Brands'), __('Brands'));
        $resultPage->addBreadcrumb(__('Brands Manager'), __('Brands Manager'));
        $resultPage->getConfig()->getTitle()->prepend(__('Brands Manager'));
        return $resultPage;
    }

}
