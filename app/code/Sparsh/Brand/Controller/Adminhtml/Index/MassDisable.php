<?php
/**
 * Class MassDisable
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
use Magento\Ui\Component\MassAction\Filter;
use Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDisable
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class MassDisable extends \Magento\Backend\App\Action
{
    /**
     * UiMassActionFilter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * BrandCollectionFactory
     *
     * @var \Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDisable Contrsuctor
     *
     * @param Context           $context           context
     * @param Filter            $filter            UiMassActionFilter
     * @param CollectionFactory $collectionFactory BrandCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sparsh_Brand::save');
    }

    /**
     * MassDisable Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );

        foreach ($collection as $item) {
            $item->setStatus(false);
            $this->saveItem($item);
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been disabled.', $collection->getSize())
        );

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Save Item
     *
     * @param CollectionFactory $item item
     *
     * @return void
     */
    public function saveItem($item)
    {
        $item->save();
    }
}
