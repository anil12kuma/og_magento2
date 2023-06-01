<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Brand\Import;

/**
 * Class Tab GeneralTab
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'      => 'edit_form',
                    'action'  => $this->getUrl('*/*/importProcess'),
                    'method'  => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('Import Information')]);

        $fieldset->addField(
            'is_update',
            'select',
            [
                'title'  => __('Import Behavior'),
                'label'  => __('Import Behavior'),
                'name'   => 'is_update',
                'values' => [
                    [
                        'value' => 1,
                        'label' => __('Import & Replace Existing Data'),
                    ],
                    [
                        'value' => 0,
                        'label' => __('Import & Keep Existing Data'),
                    ],
                ],
            ]
        );

        $fieldset->addField(
            'csv_brand',
            'file',
            [
                'title'    => __('Import File'),
                'label'    => __('Import File'),
                'name'     => 'csv_brand',
                'required' => true,
                'note'     => 'Only csv file is supported. Click <a target="_blank" href="'
                    . $this->getUrl('shopbybrand/brand/sampleFile')
                    . '">here</a> to download the Sample CSV file',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
