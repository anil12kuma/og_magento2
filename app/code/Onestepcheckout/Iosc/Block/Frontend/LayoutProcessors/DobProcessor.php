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
namespace Onestepcheckout\Iosc\Block\Frontend\LayoutProcessors;

class DobProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Onestepcheckout\Iosc\Helper\Data $helper
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Ui\Component\Form\AttributeMapper $attributeMapper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Onestepcheckout\Iosc\Helper\Data $helper,
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Ui\Component\Form\AttributeMapper $attributeMapper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->localeDate = $localeDate;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {

        if ($this->helper->isEnabled()) {
            $jsLayout = $this->getDobLayout($jsLayout);
        }

        return $jsLayout;
    }

    /**
     *
     * @param array $jsLayout
     * @return array
     */
    public function getDobLayout($jsLayout)
    {
        $isLoggedIn = $this->customerSession->getId();
        if ($isLoggedIn) {
            if ($this->customerSession->getCustomer()->getDob()) {
                return $jsLayout;
            }
        }

        $customAttributeCode = 'dob';
        $shared = false;

        if ($this->checkoutSession->getQuote()->getIsVirtual()) {
            $shared = $jsLayout['components']['checkout']
            ['children']['steps']
            ['children']['billing-step']
            ['children']['payment']
            ['children']['afterMethods']
            ['children']['billing-address-form']
            ['dataScopePrefix'] ?? false;

            if ($shared) {
                $customScope = $shared;
            } else {
                $customScope = 'billingAddressfree';
            }
        } else {
            $customScope = 'shippingAddress';
        }

        $customField = [
            'component' => 'Magento_Ui/js/form/element/date',
            'config' => [
                'customScope' => $customScope,
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/date',
                'additionalClasses' => 'validate-dob',
            ],
            'options' => [
                'changeMonth' => true,
                'changeYear' => true,
                'maxDate' => '0d',
                'showOn' => 'both',
                'yearRange' => '-90y:c+nn',
                'dateFormat' => $this->localeDate->getDateFormatWithLongYear()
            ],
            'dataScope' => $customScope .'.extension_attributes' . '.' . $customAttributeCode,
            'label' => 'Date of Birth',
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
        ];

        $attribute = $this->getCustomerAttribute($customAttributeCode);

        if (isset($attribute[$customAttributeCode])) {
            $customField = array_merge($customField, $attribute[$customAttributeCode]);
        }

        if (isset($customField['options']['0']['label']) &&
            empty($customField['options']['0']['label'])
        ) {
            $customField['options']['0']['label'] = " ";
        }

        $jsLayout['components']['checkout']
            ['children']['steps']
            ['children']['shipping-step']
            ['children'][$customScope]
            ['children']['shipping-address-fieldset']
            ['children'][$customAttributeCode] = $customField;

        if ($this->checkoutSession->getQuote()->getIsVirtual()) {
            if ($shared) {
                $jsLayout['components']['checkout']
                ['children']['steps']
                ['children']['billing-step']
                ['children']['payment']
                ['children']['afterMethods']
                ['children']['billing-address-form']
                ['children']['form-fields']
                ['children'][$customAttributeCode] = $customField;
            } else {
                $jsLayout['components']['checkout']
                ['children']['steps']
                ['children']['billing-step']
                ['children']['payment']
                ['children']['payments-list']
                ['children']['free-form']
                ['children']['form-fields']
                ['children'][$customAttributeCode] = $customField;
            }
        }

        return $jsLayout;
    }

    private function getCustomerAttribute($customAttributeCode)
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'customer_account_edit'
        );

        $elements = [];

        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined() || $code !== $customAttributeCode) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);
            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
        }

        return $elements;
    }
}
