<?php
/**
 * OneStepCheckout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   onestepcheckout
 * @package    onestepcheckout_iosc
 * @copyright  Copyright (c) 2017 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */
namespace Onestepcheckout\Iosc\Block\Adminhtml\System\Config\Form\Field;

/**
 *
 * @inheritdoc
 */
class Endpoints extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /** @var \Magento\Framework\View\Element\BlockFactory */
    public $blockFactory;

    protected $apiRoutes;
    protected $allowedApiRoutes;
    protected $allowedFilters = ['/V1/guest', '/V1/carts', '/V1/coupon'];
    protected $_arrayRowsCache;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Onestepcheckout\Iosc\Helper\Data $helper
     * @param \Magento\Webapi\Model\ConfigInterface $apiConfig
     * @param \Magento\Webapi\Model\Config\Converter $apiConverter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Onestepcheckout\Iosc\Helper\Data $helper,
        \Magento\Webapi\Model\ConfigInterface $apiConfig,
        \Magento\Webapi\Model\Config\Converter $apiConverter,
        array $data = []
    ) {
        $this->blockFactory = $blockFactory;
        $this->helper = $helper;
        $this->apiConfig = $apiConfig;
        $this->apiConverter = $apiConverter;

        $this->setTemplate('Onestepcheckout_Iosc::system/config/form/field/rest-array.phtml');

        parent::__construct($context, $data);
    }

    /**
     *
     * @inheritdoc
     */
    public function _prepareToRender()
    {
        $elementBlockClass = \Onestepcheckout\Iosc\Block\Adminhtml\System\Config\Form\Field\FieldArray\Renderer::class;

        $this->addColumn('enabled', [
            'label' => __('Enabled'),
            'style' => 'width: 50px',
            'renderer' => $this->blockFactory->createBlock($elementBlockClass)
                ->setElementType('checkbox')
        ]);

        $this->addColumn('webapi_endpoint', [
            'label' => __('REST Endpoint'),
            'style' => 'width: 100%'
        ]);

        $this->addColumn('is_default', [
            'label' => __(""),
            'type' => 'hidden'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Fields');
    }

    /**
     *
     * @return array
     */
    protected function getApiRoutes()
    {
        if (!$this->apiRoutes) {
            $this->apiRoutes = $this->apiConfig
                ->getServices()[$this->apiConverter::KEY_ROUTES];
        }
        return $this->apiRoutes;
    }

    protected function getAllowedApiRoutes()
    {
        if (!$this->allowedApiRoutes) {
            $apiRoutes = $this->getApiRoutes();
            $allowedFilters = $this->allowedFilters;
            $this->allowedApiRoutes = array_filter(
                $apiRoutes,
                function ($val, $key) use ($allowedFilters) {
                    foreach ($allowedFilters as $needle) {
                        $trimNeedle = trim($needle, '/');
                        $trimPath = (string) trim($key, '/');
                        $match = strpos($trimPath, $trimNeedle);
                        if ($match === 0) {
                            break;
                        }
                    }
                    return ($match === 0) ? true : false;
                },
                ARRAY_FILTER_USE_BOTH
            );

            ksort($this->allowedApiRoutes, SORT_STRING);
        }
        return $this->allowedApiRoutes;
    }

    /**
     *
     * @inheritdoc
     */
    public function getArrayRows()
    {

        $values = $this->getElement()->getValue() ?? [];

        try {

            $defaultValues = array_keys($this->getAllowedApiRoutes());
            foreach ($defaultValues as $k => $v) {
                if (!isset($values[$v])) {
                    $values[$v] = [
                        'enabled' => '0',
                        'webapi_endpoint' => $v,
                        'is_default' => '1'
                    ];
                }
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\Exception $e) {
            /**
             * silence
             */
        }

        $this->getElement()->setValue($values);

        return parent::getArrayRows();
    }

    /**
     *
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);
        if ($element->getInherit() == 1 && $isCheckboxRequired) {
            $element->setDisabled(true);
        }

        if ($element->getIsDisableInheritance()) {
            $element->setReadonly(true);
        }

        $html = '<td class="empty"></td></tr>
            <tr id="row_' . $element->getHtmlId() . '_label" class="rowlabel"><td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label></td></tr>
            <tr id="row_' . $element->getHtmlId() . '">';
        $html .= $this->_renderValue($element);

        if ($isCheckboxRequired) {
            $html .= $this->_renderInheritCheckbox($element);
        }

        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     *
     * @inheritdoc
     */
    public function renderCellTemplate($columnName)
    {
        $template = parent::renderCellTemplate($columnName);
        if ($columnName == 'webapi_endpoint' && isset($this->_columns[$columnName])) {
            $template = str_replace('value=', 'data-is-default=\"<%- is_default %>\" value=', $template);

        }
        if ($columnName == 'is_default' && isset($this->_columns[$columnName])) {
            $template = str_replace('text', 'hidden', $template);

        }
        return $template;
    }

    /**
     *
     * @inheritdoc
     */
    protected function _renderHint(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        return $html;
    }

    /**
     *
     * @inheritdoc
     */
    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        return '<tr>' . $html . '</tr>';
    }
}
