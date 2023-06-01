<?php
namespace Wishtech\ProductZoom\Model\Config\Source;
/**
 * Class Cursor
 * @package Wishtech\ProductZoom\Model\Config\Source
 */
class Cursor implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return string[]
     */
    public function toOptionArray()
    {
        return [
            'default' 	=> 'Default',
            'cursor' 	=> 'Cursor',
            'crosshair' => 'Crosshair'
        ];
    }
}
