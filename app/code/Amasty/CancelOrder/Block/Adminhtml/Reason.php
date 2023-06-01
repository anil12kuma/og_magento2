<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Block\Adminhtml;

use Amasty\CancelOrder\Model\ConfigProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Reason extends Field
{
    /**
     * @var ConfigProvider
     */
    private $config;

    public function __construct(
        Context $context,
        ConfigProvider $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    protected function _construct()
    {
        $this->setTemplate('Amasty_CancelOrder::/reason.phtml');
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
    public function getReasons()
    {
        $positions = [];
        $methods = $this->getElement()->getValue();
        foreach ($methods as $key => $methodObject) {
            if (is_array($methodObject)) {
                $positions = $positions += $methodObject;
            }
        }

        return $positions;
    }

    /**
     * @param  int $index
     * @return string
     */
    public function getNamePrefix($index)
    {
        return $this->getElement()->getName() . '[' . $index . ']';
    }
}
