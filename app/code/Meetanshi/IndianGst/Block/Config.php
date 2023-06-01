<?php
namespace Meetanshi\IndianGst\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Meetanshi\IndianGst\Helper\Data;
use Meetanshi\IndianGst\Model\Config\Source\ShippingBillingAddress;
use Meetanshi\IndianGst\Model\ConfigProvider;

/**
 * Class Config
 * @package Meetanshi\IndianGst\Block
 */
class Config extends Template
{
    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Data $helper
     * @param Context $context
     * @param ConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->configProvider = $configProvider;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * @return array
     */
    public function getCustomConfig()
    {
        return $this->configProvider->getConfig();
    }

    public function isGstOnBilling(){
        return $this->helper->isGstOnBilling();
    }
    public function isEnabled(){
        return $this->helper->isEnabled();
    }
}
