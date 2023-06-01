<?php
/**
 * Class MassDelete
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
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * UiMassActionFilter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * BrandCollctionFactory
     *
     * @var \Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $brandCollectionFactory;

    /**
     * UrlRewriteFactory
     *
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * MassDelete constructor.
     *
     * @param Context                                                       $context                context
     * @param Filter                                                        $filter                 filter
     * @param \Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory $brandCollectionFactory brandCollectionFactory
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory                   $urlRewriteFactory      urlRewriteFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory $brandCollectionFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
    ) {
        $this->filter = $filter;
        $this->brandCollectionFactory = $brandCollectionFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sparsh_Brand::delete');
    }

    /**
     * MassDelete Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $brandCollection = $this->filter->getCollection(
            $this->brandCollectionFactory->create()
        );

        $brandCollectionSize = $brandCollection->getSize();

        foreach ($brandCollection as $brandItem) {
            $urlRewriteModel = $this->urlRewriteFactory->create();
            $urlRewriteData = $urlRewriteModel->getCollection()
                ->addFieldToFilter('request_path', $brandItem->getUrlKey());
            
            foreach ($urlRewriteData->getItems() as $rewrite) {
                $this->deleteRewriteItem($rewrite);
            }

            $this->deleteBrandItem($brandItem);
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been deleted.', $brandCollectionSize)
        );

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Delete Urlwrite item
     *
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $item item
     *
     * @return void
     */
    public function deleteRewriteItem($item)
    {
        $item->delete();
    }

    /**
     * Delete Brand item
     *
     * @param \Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory $item item
     *
     * @return void
     */
    public function deleteBrandItem($item)
    {
        $item->delete();
    }
}
