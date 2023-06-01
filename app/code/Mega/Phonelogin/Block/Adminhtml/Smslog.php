<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to samparker801@gmail.com so we can send you a copy immediately.
 *
 * @category    Mega
 * @package     Mega_PhoneLogin
 * @copyright   Copyright (c) 2017 Sam Parker (samparker801@gmail.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mega\Phonelogin\Block\Adminhtml;


class Smslog extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {

        $this->_controller = 'adminhtml_smslog';
        $this->_blockGroup = 'Mega_Phonelogin';
        $this->_headerText = __('SMS Log');
        parent::_construct();
        $this->removeButton('add');
    }
}