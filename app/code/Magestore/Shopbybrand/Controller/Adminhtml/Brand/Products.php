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
use Magento\Framework\Controller\ResultFactory;

class Products extends Brand
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Context
     * @var PageFactory
     */
    protected $resultPage;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Products action
     */
    public function execute()
    {
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        return $resultLayout;
    }
}