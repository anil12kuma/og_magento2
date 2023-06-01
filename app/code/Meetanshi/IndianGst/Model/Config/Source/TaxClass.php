<?php
namespace Meetanshi\IndianGst\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class TaxClass implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Exclusive of GST')],
            ['value' => 0, 'label' => __('Inclusive of GST')],
        ];
    }
}
