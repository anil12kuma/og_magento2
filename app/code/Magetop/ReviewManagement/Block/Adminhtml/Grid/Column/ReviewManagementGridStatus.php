<?php
/**
 * @Author      Magetop Team
 * @package     Review Management
 * @copyright   Copyright (c) 2019 MAGETOP (http://www.magetop.com)
 * @terms       http://www.magetop.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\ReviewManagement\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class ReviewManagementGridStatus extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getIsActive() || $row->getStatus()) {
            $cell = '<span class="grid-severity-notice"><span>Approved</span></span>';
        } else {
            $cell = '<span class="grid-severity-critical"><span>Disapproved</span></span>';
        }
        return $cell;
    }
}