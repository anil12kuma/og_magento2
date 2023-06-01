<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Controller\Action\Predispatch;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class AdminhtmlSystemConfigEdit extends \Magestore\Shopbybrand\Observer\AbstractObserver
{

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controllerAction = $observer->getControllerAction();
        if ($controllerAction->getRequest()->getParam('section') == 'ms_shopbybrand') {
            $attributeCode = $this->_brandHelper->getAttributeCode();
            $this->_objectManager->get('Magento\Backend\Model\Session')->setAttributeCode($attributeCode);
        }
    }
}