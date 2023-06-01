<?php
namespace Meetanshi\IndianGst\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ShippingBillingAddress implements ArrayInterface
{
    const SHIPPING_ADDRESS='shipping';
    const BILLING_ADDRESS='billing';
    public function toOptionArray()
    {
        return [
            ['value' => self::SHIPPING_ADDRESS, 'label' => __('Shipping Address')],
            ['value' => self::BILLING_ADDRESS, 'label' => __('Billing Address')],
        ];
    }
}
