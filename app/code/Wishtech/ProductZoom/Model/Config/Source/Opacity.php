<?php
namespace Wishtech\ProductZoom\Model\Config\Source;
/**
 * Class Opacity
 * @package Wishtech\ProductZoom\Model\Config\Source
 */
class Opacity implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            '0' 	=> 0,
            '0.1'   => 0.1,
            '0.2' 	=> 0.2,
            '0.3' 	=> 0.3,
            '0.4' 	=> 0.4,
            '0.5' 	=> 0.5,
            '0.6' 	=> 0.6,
            '0.7' 	=> 0.7,
            '0.8' 	=> 0.8,
            '0.9' 	=> 0.9,
            '1' 	=> 1
        ];
    }
}
