<?php
/**
 * @Author      Magetop Team
 * @package     Review Management
 * @copyright   Copyright (c) 2019 MAGETOP (http://www.magetop.com)
 * @terms       http://www.magetop.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\ReviewManagement\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Backend system config datetime field renderer
 */
class Info extends \Magento\Config\Block\System\Config\Form\Field
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = '<div style="background: #EF432E;padding: 10px;border-radius: 5px;text-align: center">
                    <a target="_blank" href="https://www.magetop.com/magento-extensions.html" style="color: #fff">Magetop - Marketplace Extensions</a>
                </div>';
        
        return $html;
    }
}