<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Brand\Edit;

use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shopbybrand_brand_form');
        $this->setTitle(__('General Information'));
    }

    /**
     * @return WidgetForm
     */
    protected function _prepareForm()
    {
        $params = [];
        if($this->getRequest()->getParam('store')) {
            $params['store'] = $this->getRequest()->getParam('store');
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('shopbybrand/brand/save', $params),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
