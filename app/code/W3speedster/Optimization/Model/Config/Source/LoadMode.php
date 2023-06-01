<?php


namespace W3speedster\Optimization\Model\Config\Source;

/**
 * Popup behavior mode
 */
class LoadMode implements \Magento\Framework\Data\OptionSourceInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => 'on_page_load', 'label' => __('On Page Load')],
    ['value' => 'after_page_load', 'label' => __('After Page Load')]
  ];
 }
}
