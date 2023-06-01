<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Block\Returns;

use Amasty\Rma\Controller\RegistryConstants;
use Amasty\Rma\Model\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class OrderList extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var bool
     */
    private $isGuest;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        Session $customerSession,
        Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->registry = $registry;
        $this->isGuest = !empty($data['isGuest']);
    }

    /**
     * @return array
     */
    public function getOrders()
    {
        $statuses = $this->configProvider->getAllowedOrderStatuses();
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToSelect(
            [
                OrderInterface::ENTITY_ID,
                OrderInterface::INCREMENT_ID,
                OrderInterface::CREATED_AT,
                OrderInterface::GRAND_TOTAL
            ]
        )->setOrder(OrderInterface::ENTITY_ID);
        if (!$this->isGuest()) {
            $orderCollection->addFieldToFilter(
                OrderInterface::CUSTOMER_ID,
                $this->customerSession->getCustomerId()
            );
        } else {
            $orderCollection->join(
                'sales_order_address',
                'main_table.entity_id = sales_order_address.parent_id'
                    . ' and sales_order_address.address_type = \'billing\'',
                []
            )->addFieldToFilter(
                OrderInterface::CUSTOMER_EMAIL,
                $this->registry->registry(RegistryConstants::GUEST_DATA)['email']
            )->addFieldToFilter(
                'sales_order_address.' . OrderAddressInterface::LASTNAME,
                $this->registry->registry(RegistryConstants::GUEST_DATA)['lastname']
            );
        }

        if ($statuses) {
            $orderCollection->addFieldToFilter(OrderInterface::STATUS, $statuses);
        }

        return $orderCollection->getData();
    }

    public function formatPrice($price)
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            false,
            2
        );
    }

    public function getNewReturnUrl()
    {
        if (!$this->isGuest()) {
            return $this->_urlBuilder->getUrl($this->configProvider->getUrlPrefix() .'/account/newreturn');
        }

        return $this->_urlBuilder->getUrl($this->configProvider->getUrlPrefix() . '/guest/loginpost');
    }

    public function getLogoutUrl()
    {
        return $this->_urlBuilder->getUrl($this->configProvider->getUrlPrefix() . '/guest/logout');
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->isGuest;
    }
}
