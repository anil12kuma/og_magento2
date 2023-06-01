<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Block\Adminhtml;

use Amasty\Base\Model\Serializer;
use Amasty\CancelOrder\Model\ConfigProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Json\EncoderInterface;
use Magento\Payment\Model\Config as PaymentConfig;

class Conditions extends Field
{
    const PAYMENT_METHODS = 'payment_methods';
    const DURATION = 'duration';
    const DURATION_UNIT = 'duration_unit';

    const DAY = 'day';
    const HOUR = 'hour';

    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    public function __construct(
        Context $context,
        ConfigProvider $config,
        EncoderInterface $jsonEncoder,
        Serializer $serializer,
        PaymentConfig $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->jsonEncoder = $jsonEncoder;
        $this->serializer = $serializer;
        $this->paymentConfig = $paymentConfig;
    }

    protected function _construct()
    {
        $this->setTemplate('Amasty_CancelOrder::/conditions.phtml');
    }

    /**
     * @param AbstractElement $element
     *
     * @return bool
     */
    public function isInheritCheckboxRequired(AbstractElement $element)
    {
        return $this->_isInheritCheckboxRequired($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function renderInheritCheckboxHtml(AbstractElement $element)
    {
        return $this->_renderInheritCheckbox($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function renderScopeLabel(AbstractElement $element)
    {
        return $this->_renderScopeLabel($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->_decorateRowHtml($element, $this->_toHtml());
    }

    /**
     * @return array
     */
    public function getSelectedConditions(): array
    {
        $conditions = $this->getElement()->getValue() ?: [];
        if (isset($conditions[0]) && empty($conditions[0])) {
            $conditions = [];
        }

        return $conditions;
    }

    /**
     * @return array
     */
    public function getDefaultConditions(): array
    {
        return [
            self::PAYMENT_METHODS => $this->getPaymentMethods(),
            self::DURATION => '',
            self::DURATION_UNIT => [
                Conditions::DAY => __('Day(s)'),
                Conditions::HOUR => __('Hour(s)')
            ]
        ];
    }

    /**
     * @return string
     */
    public function getUrlData(): string
    {
        return $this->jsonEncoder->encode(
            ['url' =>  $this->_urlBuilder->getUrl('am_cancel_order/cancel/index')]
        );
    }

    /**
     * @return array
     */
    public function getPaymentMethods(): array
    {
        $methods = $this->paymentConfig->getActiveMethods();
        foreach ($methods as &$method) {
            $method = $method->getTitle();
        }

        return $methods ?? [];
    }

    /**
     * @return bool|string
     */
    public function getInitData()
    {
        return $this->serializer->serialize(
            [
                'namePrefix' => $this->getNamePrefix('#'),
                self::PAYMENT_METHODS => $this->getPaymentMethods(),
                self::DURATION_UNIT => $this->getDefaultConditions()[self::DURATION_UNIT]
            ]
        );
    }

    /**
     * @param int $index
     * @param int|null $counter
     * @return string
     */
    public function getNamePrefix($index, $counter = null)
    {
        $name = str_replace('[]', '', $this->getElement()->getName());
        $name .= $counter !== null ? '[' . $counter . ']' : '';

        return $name . '[' . $index . ']';
    }
}
