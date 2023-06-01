<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\CancelOrder\Block\Adminhtml\Conditions;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\CollectionDataSourceInterface;

class ConfigProvider extends ConfigProviderAbstract implements CollectionDataSourceInterface
{
    const CUSTOMER_GROUP = 'general/customer_group';
    const ENABLED = 'general/enabled';
    const ORDER_STATUS = 'general/order_status';
    const REFUND = 'general/refund';
    const BUTTON_TITLE = 'frontend/button_title';
    const REASON = 'frontend/reason';
    const POPUP_ENABLED = 'frontend/popup_enabled';
    const NOTICE = 'frontend/notice';
    const REQUIRED = 'frontend/required';
    const ADMIN_NOTIFICATION_ENABLED = 'admin_email/enabled';
    const ADMIN_TO = 'admin_email/to';
    const ADMIN_SENDER = 'admin_email/sender';
    const ADMIN_TEMPLATE = 'admin_email/template';
    const AUTO_NOTIFICATION_ENABLED = 'admin_email/auto_canceled_enabled';
    const AUTO_TO = 'admin_email/auto_canceled_to';
    const AUTO_SENDER = 'admin_email/auto_canceled_sender';
    const AUTO_TEMPLATE = 'admin_email/auto_canceled_template';
    const AUTO_CANCEL_ENABLED = 'auto_cancel_mode/enabled';
    const AUTO_CANCEL_FROM = 'auto_cancel_mode/created_from';
    const AUTO_CANCEL_STATUS = 'auto_cancel_mode/order_status';
    const AUTO_CANCEL_CONDITIONS = 'auto_cancel_mode/conditions';

    /**
     * @var string
     */
    protected $pathPrefix = 'am_cancel_order/';

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        parent::__construct($scopeConfig);
        $this->filterManager = $filterManager;
        $this->serializer = $serializer;
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getReasons($storeId = null)
    {
        $value = $this->getValue(self::REASON, $storeId);
        if ($value) {
            $reasons = [];
            $value = $this->serializer->unserialize($value);
            $value = array_filter($value);
            foreach ($value as $item) {
                $reasons += $item;
            }
        }

        return $reasons ?? [];
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getValue(self::ENABLED);
    }

    /**
     * @return bool
     */
    public function isPopupEnabled()
    {
        return (bool)$this->getValue(self::POPUP_ENABLED);
    }

    /**
     * @return bool
     */
    public function isAdminNotificationEnabled()
    {
        return (bool)$this->getValue(self::ADMIN_NOTIFICATION_ENABLED);
    }

    /**
     * @return string
     */
    public function getNotice()
    {
        return $this->filterManager->stripTags(
            $this->getValue(self::NOTICE),
            [
                'allowableTags' => null,
                'escape' => true
            ]
        );
    }

    /**
     * @return string
     */
    public function getRefundType()
    {
        return $this->getValue(self::REFUND);
    }

    /**
     * @return array
     */
    public function getEnabledCustomerGroups()
    {
        $value = $this->getValue(self::CUSTOMER_GROUP);
        $value = $value ? explode(',', $value) : [];

        return $value;
    }

    /**
     * @param string $element
     *
     * @return bool
     */
    public function isElementRequired(string $element)
    {
        $value = $this->getValue(self::REQUIRED);
        $value = $value ? explode(',', $value) : [];

        return in_array($element, $value);
    }

    /**
     * @return string
     */
    public function getButtonTitle()
    {
        return $this->filterManager->stripTags(
            $this->getValue(self::BUTTON_TITLE),
            [
                'allowableTags' => null,
                'escape' => true
            ]
        );
    }

    /**
     * @return array
     */
    public function getEnabledOrderStatuses()
    {
        $value = $this->getValue(self::ORDER_STATUS);
        $value = $value ? explode(',', $value) : [];

        return $value;
    }

    /**
     * @return array
     */
    public function getAdminNotificationTo()
    {
        $value = trim($this->getValue(self::ADMIN_TO));
        $value = $value ? explode(',', $value) : [];

        return $value;
    }

    /**
     * @return string
     */
    public function getAdminSender()
    {
        return (string)$this->getValue(self::ADMIN_SENDER);
    }

    /**
     * @return string
     */
    public function getAdminTemplate()
    {
        return (string)$this->getValue(self::ADMIN_TEMPLATE);
    }

    /**
     * @return bool
     */
    public function isAutoCancelEnabled()
    {
        return (bool)$this->getValue(self::AUTO_CANCEL_ENABLED);
    }

    /**
     * @return string
     */
    public function getAutoCancelFrom()
    {
        return $this->getValue(self::AUTO_CANCEL_FROM);
    }

    /**
     * @return string
     */
    public function getAutoCancelStatus()
    {
        return $this->getValue(self::AUTO_CANCEL_STATUS);
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function getAutoCancelConditions()
    {
        return $this->serializer->unserialize($this->getValue(self::AUTO_CANCEL_CONDITIONS));
    }

    /**
     * @return bool
     */
    public function isAutoNotificationEnabled()
    {
        return (bool)$this->getValue(self::AUTO_NOTIFICATION_ENABLED);
    }

    /**
     * @return array
     */
    public function getAutoNotificationTo()
    {
        $value = trim($this->getValue(self::AUTO_TO));
        $value = $value ? explode(',', $value) : [];

        return $value;
    }

    /**
     * @return string
     */
    public function getAutoSender()
    {
        return (string)$this->getValue(self::AUTO_SENDER);
    }

    /**
     * @return string
     */
    public function getAutoTemplate()
    {
        return (string)$this->getValue(self::AUTO_TEMPLATE);
    }
}
