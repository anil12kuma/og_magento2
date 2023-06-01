<?php
/**
 * Class Edit
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

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\Session;
use Magento\Framework\Registry;

/**
 * Class Edit
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * PageFactory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * BrandFactory
     *
     * @var \Sparsh\Brand\Model\BrandFactory
     */
    protected $brand;

    /**
     * SessionModel
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $sessionData;

    /**
     * Edit Constructor
     *
     * @param Action\Context $context           context
     * @param PageFactory    $resultPageFactory pageFactory
     * @param BrandFactory   $brand             brandModel
     * @param Session        $session           sessionModel
     * @param Registry       $registry          coreRegistry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Sparsh\Brand\Model\BrandFactory $brand,
        Session $session,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->brand = $brand;
        $this->sessionData = $session;
    }
    
    /**
     * {@inheritdoc}
     *
     @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sparsh_Brand::save');
    }
    
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('brand_id');
        $model = $this->brand->create();

        if ($id) {
            $model = $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Something went wrong.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->sessionData->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('brands_index', $model);

        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Athelete') : __('New Athelete'),
            $id ? __('Edit Athelete') : __('New Athelete')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Athelete'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Athelete'));

        return $resultPage;
    }
}
