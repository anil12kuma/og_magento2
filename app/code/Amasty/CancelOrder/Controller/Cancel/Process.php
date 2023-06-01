<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Controller\Cancel;

use Amasty\CancelOrder\Api\Data\CancelOrderInterface;
use Amasty\CancelOrder\Model\CancelOrderProcess;
use Amasty\CancelOrder\Model\Validation;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Psr\Log\LoggerInterface;

class Process extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderLoaderInterface
     */
    private $orderLoader;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var Validation
     */
    private $validation;

    /**
     * @var CancelOrderProcess
     */
    private $cancelOrderProcess;

    public function __construct(
        Context $context,
        OrderLoaderInterface $orderLoader,
        Registry $registry,
        UrlInterface $urlBuilder,
        CancelOrderProcess $cancelOrderProcess,
        FilterManager $filterManager,
        Validation $validation,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderLoader = $orderLoader;
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        $this->filterManager = $filterManager;
        $this->validation = $validation;
        $this->cancelOrderProcess = $cancelOrderProcess;
    }

    /**
     * Action for reorder
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof ResultInterface) {
            return $result;
        }

        $this->compatibilityWithStoreCredit();
        $order = $this->registry->registry('current_order');
        try {
            if ($this->validation->validateOrderAndSettings($order)) {
                $data = $this->validateData($this->getRequest()->getParams());
                $result = $this->cancelOrderProcess->execute($order, $data);
            }

            if ($result ?? false) {
                $this->messageManager->addSuccessMessage(__('Your order has been canceled successfully.'));
            } else {
                $this->messageManager->addErrorMessage(__('You can`t cancel the order.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('You can`t cancel the order. Message:') . $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('You have not canceled the order.'));
            $this->logger->critical($e);
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setUrl(
            $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()])
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function validateData(array $data)
    {
        foreach ([CancelOrderInterface::COMMENT, CancelOrderInterface::REASON] as $item) {
            $data[$item] = $this->filterManager->stripTags(
                $data[$item] ?? '',
                [
                    'allowableTags' => null,
                    'escape' => true
                ]
            );
        }

        return $data;
    }

    protected function compatibilityWithStoreCredit()
    {
        $params = $this->getRequest()->getParams();
        $params['return_to_store_credit'] = 0;

        $this->getRequest()->setParams($params);
    }
}
