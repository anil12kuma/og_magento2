<?php
/**
 * Class Index
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     *
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * Index Constructor
     *
     * @param Context     $context           context
     * @param PageFactory $resultPageFactory pageFactory
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
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Sparsh_Brand::brand');
        $resultPage->addBreadcrumb(__('Athelets'), __('Athelets'));
        $resultPage->addBreadcrumb(__('Manage Athelets'), __('Manage Athelets'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Athelets'));

        return $resultPage;
    }

    /**
     * Is the user allowed to view the brand grid.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sparsh_Brand::brand');
    }
}
