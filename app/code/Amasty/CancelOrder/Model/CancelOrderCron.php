<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Model;

use Amasty\CancelOrder\Api\Data\CancelOrderInterface;
use Amasty\CancelOrder\Block\Adminhtml\Conditions;
use Amasty\CancelOrder\Model\Source\CanceledBy;
use Magento\Sales\Api\Data\OrderInterface;
use \Magento\Framework\DataObjectFactory as ObjectFactory;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class CancelOrderCron
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var CancelOrderProcess
     */
    private $cancelOrderProcess;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    public function __construct(
        CancelOrderProcess $cancelOrderProcess,
        ConfigProvider $configProvider,
        OrderCollectionFactory $orderCollectionFactory,
        \Magento\Payment\Helper\Data $paymentHelper,
        ObjectFactory $objectFactory
    ) {
        $this->configProvider = $configProvider;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->cancelOrderProcess = $cancelOrderProcess;
        $this->paymentHelper = $paymentHelper;
        $this->objectFactory = $objectFactory;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function execute()
    {
        $count = 0;
        if ($this->configProvider->isAutoCancelEnabled()) {
            $methods = $this->getMethods();
            $orderCollection = $this->getOrderCollection();
            $size = $orderCollection->getSize();
            foreach ($orderCollection as $order) {
                if ($this->isNeedCancel($order, $methods)) {
                    $data = $this->getOrderData($order);
                    if ($this->cancelOrderProcess->execute($order, $data)) {
                        $count++;
                    }
                }
            }
        }

        $data = [
            'totalCount' => $size,
            'canceledCount' => $count
        ];

        return $this->objectFactory->create(['data' => $data]);
    }

    /**
     * @param OrderInterface $order
     * @param array $methods
     * @return bool
     */
    private function isNeedCancel($order, $methods)
    {
        $paymentMethod = $order->getPayment()->getMethod();

        return isset($methods[$paymentMethod])
            && $this->getTimeCondition($methods[$paymentMethod]) >= $order->getData('created_at');
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    private function getOrderData(OrderInterface $order)
    {
        $paymentMethod = $order->getPayment()->getMethod();

        return [
            CancelOrderInterface::REASON => '',
            CancelOrderInterface::COMMENT => '',
            CancelOrderInterface::ORDER_ID => $order->getId(),
            CancelOrderInterface::ORDER_CREATED_AT => $order->getData('created_at'),
            'canceled_at' => \Date('Y-m-d H:i:s'),
            'payment_method' => $this->paymentHelper->getPaymentMethodList()[$paymentMethod],
            CancelOrderInterface::STATUS => CanceledBy::AUTO_CANCEL
        ];
    }

    /**
     * @return array
     */
    private function getMethods()
    {
        $conditions = $this->configProvider->getAutoCancelConditions() ?: [];
        foreach ($conditions as $condition) {
            if (isset($condition[Conditions::PAYMENT_METHODS])) {
                $methods[$condition[Conditions::PAYMENT_METHODS]] = $condition;
            }
        }

        return $methods ?? [];
    }

    /**
     * @param array $condition
     * @return false|string
     */
    private function getTimeCondition(array $condition)
    {
        return \Date(
            'Y-m-d H:i:s',
            strtotime('-' . $condition['duration'] . ' ' . $condition['duration_unit'])
        );
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private function getOrderCollection()
    {
        return $this->orderCollectionFactory->create()
            ->addAttributeToFilter(
                CancelOrderInterface::CREATED_AT,
                ['gteq' => $this->configProvider->getAutoCancelFrom()]
            )
            ->addAttributeToFilter(
                CancelOrderInterface::STATUS,
                ['in' => $this->configProvider->getAutoCancelStatus()]
            )
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('customer_email')
            ->addFieldToSelect(CancelOrderInterface::STATUS)
            ->addFieldToSelect(CancelOrderInterface::CREATED_AT);
    }
}
