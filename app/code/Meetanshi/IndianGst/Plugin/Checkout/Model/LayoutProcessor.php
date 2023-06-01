<?php

namespace Meetanshi\IndianGst\Plugin\Checkout\Model;

use Magento\Checkout\Block\Checkout\LayoutProcessor as ChekcoutLayerprocessor;
use Meetanshi\IndianGst\Helper\Data;


class LayoutProcessor
{
    protected $dataHelper;

    public function __construct(
        Data $dataHelper
    ) {

        $this->dataHelper = $dataHelper;
    }

    public function afterProcess(
        ChekcoutLayerprocessor $subject,
        array $jsLayout
    ) {
        if (!$this->dataHelper->isEnabled()) {
            return $jsLayout;
        }

        $flag = false;
        if ($this->dataHelper->getBuyerGst()) {
            $flag = true;
        }

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['buyer_gst_number'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'gst_number'
            ],
            'dataScope' => 'shippingAddress.buyer_gst_number',
            'label' => 'GST Number#',
            'provider' => 'checkoutProvider',
            'visible' => $flag,
            'validation' => [],
            'sortOrder' => 252,
            'id' => 'buyer_gst_number'
        ];


        $configuration = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
        foreach ($configuration as $paymentGroup => $groupConfig) {
            if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['company_tax_id'] = [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'billingAddress.custom_attributes',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'options' => [],
                        'id' => 'custom-field'
                    ],
                    'dataScope' => 'billingAddress.custom_attributes.buyer_gst_number_billing',
                    'label' => 'GST Number#',
                    'provider' => 'checkoutProvider',
                    'visible' => $flag,
                    'validation' => [],
                    'sortOrder' => 250,
                    'id' => 'buyer_gst_number_billing'
                ];
            }
        }

        return $jsLayout;
    }
}
