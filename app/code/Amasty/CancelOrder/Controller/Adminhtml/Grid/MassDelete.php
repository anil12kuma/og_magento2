<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Controller\Adminhtml\Grid;

use Amasty\CancelOrder\Model\ResourceModel\Collection;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_CancelOrder::grid';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\CancelOrder\Model\Repository\CancelOrderRepository
     */
    private $repository;

    /**
     * @var \Amasty\CancelOrder\Model\ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\CancelOrder\Model\CancelOrderFactory
     */
    private $modelFactory;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        \Amasty\CancelOrder\Model\Repository\CancelOrderRepository $repository,
        \Amasty\CancelOrder\Model\ResourceModel\CollectionFactory $collectionFactory,
        \Amasty\CancelOrder\Model\CancelOrderFactory $modelFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $collectionSize = $collection->getSize();
        if ($collectionSize) {
            try {
                foreach ($collection->getItems() as $model) {
                    $this->repository->delete($model);
                }

                $this->messageManager->addSuccessMessage($this->getSuccessMessage($collectionSize));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (CouldNotSaveException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        }
        $this->_redirect($this->_redirect->getRefererUrl());
    }

    /**
     * @param int $collectionSize
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been deleted.', $collectionSize);
        }

        return __('No records have been deleted.');
    }
}
