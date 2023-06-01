<?php
namespace Wishtech\ProductZoom\Block;
use Magento\Framework\View\Element\Template;

/**
 * Class Zoom
 * @package Wishtech\ProductZoom\Block
 */
class Zoom extends \Magento\Framework\View\Element\Template
{
    protected $generalData = null;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var \Wishtech\ProductZoom\Helper\Data
     */
    protected $dataHelper;

    /**
     * Zoom constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Wishtech\ProductZoom\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Wishtech\ProductZoom\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return array|\Magento\Framework\App\Config\ScopeConfigInterface|mixed|null
     */
    protected function getGeneralData()
    {
        if (is_null($this->generalData)) {
            $this->generalData = $this->dataHelper->getConfigModule('general');
        }

        return $this->generalData;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        $data = $this->getGeneralData();
        if (is_array($data)) {
            return $this->jsonHelper->jsonEncode($data);
        }

        return '{}';
    }

    /**
     * @return bool
     */
    public function isZoomTypeWindow()
    {
        $data = $this->getGeneralData();
        if (isset($data['zoomType']) && $data['zoomType'] == 'window') {
            return true;
        }

        return false;
    }
}
