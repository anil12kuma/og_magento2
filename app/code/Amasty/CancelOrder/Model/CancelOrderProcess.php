<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model;

use Amasty\CancelOrder\Api\Data\CancelOrderInterface;
use Amasty\CancelOrder\Model\Source\CanceledBy;
use Amasty\CancelOrder\Model\Source\Refund as RefundSource;
use Amasty\CancelOrder\Model\Repository\CancelOrderRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\Validation\RefundOrderInterface as RefundOrderValidator;
use Magento\Sales\Model\RefundOrder;

class CancelOrderProcess
{
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var RefundOrder
     */
    private $refundOrder;

    /**
     * @var CancelOrderRepository
     */
    private $cancelOrderRepository;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var CreditmemoManagementInterface
     */
    private $creditmemoManagement;

    /**
     * @var CreditmemoDocumentFactory
     */
    private $creditmemoDocumentFactory;

    /**
     * @var RefundOrderValidator
     */
    private $validator;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        OrderManagementInterface $orderManagement,
        RefundOrder $refundOrder,
        CancelOrderRepository $cancelOrderRepository,
        EmailSender $emailSender,
        CreditmemoManagementInterface $creditmemoManagement,
        CreditmemoDocumentFactory $creditmemoDocumentFactory,
        RefundOrderValidator $validator,
        ConfigProvider $configProvider
    ) {
        $this->orderManagement = $orderManagement;
        $this->refundOrder = $refundOrder;
        $this->cancelOrderRepository = $cancelOrderRepository;
        $this->emailSender = $emailSender;
        $this->creditmemoManagement = $creditmemoManagement;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->validator = $validator;
        $this->configProvider = $configProvider;
    }

    /**
     * @param OrderInterface $order
     * @param array $data
     *
     * @return bool
     */
    public function execute(OrderInterface $order, array $data)
    {
        if ($order->getStatus() == Order::STATE_PROCESSING && $order->canCreditmemo()) {
            $result = $this->refund($order);
        } else {
            $result = $this->orderManagement->cancel($order->getEntityId());
        }

        if ($result) {
            $model = $this->saveRequest($data);
            $this->addCancelMessage($model, $order);
            $this->copyDataFromOrder($model, $order);
            $this->sendNotification($model);
        }

        return (bool)$result;
    }

    /**
     * @param CancelOrderInterface $model
     */
    protected function sendNotification(CancelOrderInterface $model)
    {
        if ($model->getData(CancelOrderInterface::STATUS) == CanceledBy::AUTO_CANCEL) {
            $this->emailSender->sendAutomaticNotification($model);
        } else {
            $this->emailSender->sendAdminNotification($model);
        }
    }

    /**
     * Try to refund like save creditmemo/ order way - $this->refundOrder->execute($order->getEntityId());
     *
     * @param OrderInterface $order
     *
     * @return bool
     * @throws \Magento\Sales\Exception\DocumentValidationException
     */
    protected function refund(OrderInterface $order)
    {
        $invoice = false;
        if ($this->configProvider->getRefundType() == RefundSource::ONLINE
            && $order->getInvoiceCollection()->getSize() == 1
        ) {
            $invoice = $order->getInvoiceCollection()->getFirstItem();
            $orderPayment = $order->getPayment();
            if (!($orderPayment->canRefundPartialPerInvoice()
                && $invoice->canRefund()
                && $orderPayment->getAmountPaid() > $orderPayment->getAmountRefunded()
                || $orderPayment->canRefund() && !$invoice->getIsUsedForRefund()
            )) {
                //see app/code/Magento/Sales/Block/Adminhtml/Order/Invoice/View.php
                $invoice = false;
            }
        }

        if ($invoice && $invoice->getEntityId()) {
            $creditMemo = $this->creditmemoDocumentFactory->createFromInvoice($invoice, [], null, false, null);
        } else {
            $creditMemo = $this->creditmemoDocumentFactory->createFromOrder($order, [], null, false, null);
        }

        $validationMessages = $this->validator->validate($order, $creditMemo, [], false, false, null, null);
        if ($validationMessages->hasMessages()) {
            throw new \Magento\Sales\Exception\DocumentValidationException(
                __("Creditmemo Document Validation Error(s):\n" . implode("\n", $validationMessages->getMessages()))
            );
        }
        $isOffline = true;

        //see app/code/Magento/Sales/Block/Adminhtml/Order/Creditmemo/Create/Items.php
        if ($creditMemo->getInvoice() && $creditMemo->canRefund() && $creditMemo->getInvoice()->getTransactionId()) {
            $isOffline = false;
            try {
                return (bool)$this->creditmemoManagement->refund($creditMemo, (bool)$isOffline);
            } catch (LocalizedException $exception) {
                $isOffline = true;
                $creditMemo = $this->creditmemoDocumentFactory->createFromOrder($order, [], null, false, null);
            }
        }

        return (bool)$this->creditmemoManagement->refund($creditMemo, (bool)$isOffline);
    }

    /**
     * @param array $data
     *
     * @return CancelOrderInterface
     */
    protected function saveRequest(array $data)
    {
        $model = $this->cancelOrderRepository->createNew();
        $model->addData($data);

        return $this->cancelOrderRepository->save($model);
    }

    /**
     * @param CancelOrderInterface $model
     * @param OrderInterface $order
     */
    protected function copyDataFromOrder(CancelOrderInterface $model, OrderInterface $order)
    {
        foreach (['customer_email', 'increment_id', 'customer_name'] as $item) {
            $model->setData($item, $order->getData($item));
        }
    }

    /**
     * @param CancelOrderInterface $model
     * @param OrderInterface $order
     */
    protected function addCancelMessage(CancelOrderInterface $model, OrderInterface $order)
    {
        if ($model->getData(CancelOrderInterface::STATUS) == CanceledBy::AUTO_CANCEL) {
            $message = __('Order was cancelled automatically.');
        } else {
            $message = __('Order was cancelled by customer.');
        }

        if ($model->getReason() || $model->getComment()) {
            $message .= implode('; ', [$model->getReason(), $model->getComment()]);
        }

        /** @var OrderStatusHistoryInterface $history */
        $history = $order->addStatusHistoryComment($message, Order::STATE_CANCELED);
        $history->setIsVisibleOnFront(true);
        $history->setIsCustomerNotified(true);
        $history->save();

        $order->setState(Order::STATE_CANCELED);
        $order->save();
    }
}
