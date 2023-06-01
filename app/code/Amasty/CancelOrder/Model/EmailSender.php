<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model;

use Amasty\CancelOrder\Api\Data\CancelOrderInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class EmailSender
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBackendBuilder;

    public function __construct(
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        ConfigProvider $config,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBackendBuilder
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->urlBackendBuilder = $urlBackendBuilder;
    }

    /**
     * @param CancelOrderInterface $model
     */
    public function sendAdminNotification(CancelOrderInterface $model)
    {
        $emailTo = $this->config->getAdminNotificationTo();
        if ($this->config->isAdminNotificationEnabled() && $emailTo) {
            $this->sendEmail(
                $model,
                $this->config->getAdminSender(),
                $emailTo,
                $this->config->getAdminTemplate()
            );
        }
    }

    /**
     * @param CancelOrderInterface $model
     */
    public function sendAutomaticNotification(CancelOrderInterface $model)
    {
        $emailTo = $this->config->getAutoNotificationTo();
        if ($this->config->isAutoNotificationEnabled() && $emailTo) {
            $this->sendEmail(
                $model,
                $this->config->getAutoSender(),
                $emailTo,
                $this->config->getAutoTemplate()
            );
        }
    }

    /**
     * @param CancelOrderInterface $model
     * @param $sender
     * @param array $emailTo
     * @param $template
     */
    private function sendEmail(CancelOrderInterface $model, $sender, $emailTo, $template)
    {
        try {
            $store = $this->storeManager->getStore();
            $first = array_shift($emailTo);
            $data =  [
                'website_name'  => $store->getWebsite()->getName(),
                'group_name'    => $store->getGroup()->getName(),
                'store_name'    => $store->getName(),
                'store'         => $store,
                'request'       => $model,
                'order_view_url' => $this->getOrderViewUrl($model)
            ];
            $customerEmail = $model->getCustomerEmail();

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
            )->setTemplateVars(
                $data
            )->setFrom(
                $sender
            )->addTo(
                $first
            )->addTo(
                $emailTo
            );

            if ($customerEmail) {
                $transport->setReplyTo(
                    $customerEmail
                );
            }

            $transport->getTransport()->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param CancelOrderInterface $model
     *
     * @return string
     */
    private function getOrderViewUrl(CancelOrderInterface $model)
    {
        return $this->urlBackendBuilder->getUrl(
            'sales/order/view',
            ['order_id' => $model->getOrderId()]
        );
    }
}
