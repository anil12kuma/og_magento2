<?php
namespace Wishtech\ProductZoom\Model\Config\Source;
/**
 * Class Type
 * @package Wishtech\ProductZoom\Model\Config\Source
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return string[]
     */
    public function toOptionArray()
    {
        return [
            'window' 	=> 'Window',
            'inner' 	=> 'Inner',
            'lens' 		=> 'Lens'
        ];
    }
}
