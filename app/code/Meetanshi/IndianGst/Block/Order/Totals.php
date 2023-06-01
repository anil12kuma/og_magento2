<?php

namespace Meetanshi\IndianGst\Block\Order;

use Magento\Framework\DataObject;
use Meetanshi\IndianGst\Helper\Data;

/**
 * Class Totals
 * @package Meetanshi\IndianGst\Block\Order
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->helper = $helper;
    }

    /**
     * @return $this
     */
    protected function _initTotals()
    {

        $source = $this->getSource();
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $_objectManager->create('Meetanshi\IndianGst\Helper\Data');
        $status = $helper->isEnabled();
        $isShipping = $helper->isShippingGst();
        $this->_totals = [];
        $this->_totals['subtotal'] = new DataObject([
            'code' => 'subtotal',
            'value' => $source->getSubtotal(),
            'label' => __('Subtotal')
        ]);

        if (!$source->getIsVirtual() && ((double)$source->getShippingAmount() || $source->getShippingDescription())) {
            $this->_totals['shipping'] = new DataObject([
                'code' => 'shipping',
                'field' => 'shipping_amount',
                'value' => $this->getSource()->getShippingAmount(),
                'label' => __('Shipping & Handling'),
            ]);
        }

        if ($status == 1) {
            if ($this->getSource()->getCgstAmount() && $this->getSource()->getSgstAmount()) {
                $this->_totals['cgst_amount'] = new DataObject([
                    'code' => 'cgst_amount',
                    'field' => 'cgst_amount',
                    'value' => $this->getSource()->getCgstAmount(),
                    'label' => __('CGST'),
                ]);

                $this->_totals['sgst_amount'] = new DataObject([
                    'code' => 'sgst_amount',
                    'field' => 'sgst_amount',
                    'value' => $this->getSource()->getSgstAmount(),
                    'label' => __('SGST'),
                ]);
            } elseif ($this->getSource()->getCgstAmount() && $this->getSource()->getUtgstAmount()) {
                $this->_totals['cgst_amount'] = new DataObject([
                    'code' => 'cgst_amount',
                    'field' => 'cgst_amount',
                    'value' => $this->getSource()->getCgstAmount(),
                    'label' => __('CGST'),
                ]);

                $this->_totals['utgst_amount'] = new DataObject([
                    'code' => 'utgst_amount',
                    'field' => 'utgst_amount',
                    'value' => $this->getSource()->getSgstAmount(),
                    'label' => __('UTGST'),
                ]);
            } else {
                $this->_totals['igst_amount'] = new DataObject([
                    'code' => 'igst_amount',
                    'field' => 'igst_amount',
                    'value' => $this->getSource()->getIgstAmount(),
                    'label' => __('IGST'),
                ]);
            }
        }

        if ($status && $isShipping) {
            if ($this->getSource()->getShippingCgstAmount() && $this->getSource()->getShippingSgstAmount()) {
                $this->_totals['shipping_cgst_amount'] = new DataObject([
                    'code' => 'shipping_cgst_amount',
                    'field' => 'shipping_cgst_amount',
                    'value' => $this->getSource()->getShippingCgstAmount(),
                    'label' => __('Shipping CGST'),
                ]);

                $this->_totals['shipping_sgst_amount'] = new DataObject([
                    'code' => 'shipping_sgst_amount',
                    'field' => 'shipping_sgst_amount',
                    'value' => $this->getSource()->getShippingSgstAmount(),
                    'label' => __('Shipping SGST'),
                ]);
            } elseif ($this->getSource()->getShippingCgstAmount() && $this->getSource()->getShippingUtgstAmount()) {
                $this->_totals['shipping_cgst_amount'] = new DataObject([
                    'code' => 'shipping_cgst_amount',
                    'field' => 'shipping_cgst_amount',
                    'value' => $this->getSource()->getShippingCgstAmount(),
                    'label' => __('Shipping CGST'),
                ]);

                $this->_totals['shipping_utgst_amount'] = new DataObject([
                    'code' => 'shipping_utgst_amount',
                    'field' => 'shipping_utgst_amount',
                    'value' => $this->getSource()->getShippingSgstAmount(),
                    'label' => __('Shipping UTGST'),
                ]);
            } else {
                $this->_totals['shipping_igst_amount'] = new DataObject([
                    'code' => 'shipping_igst_amount',
                    'field' => 'shipping_igst_amount',
                    'value' => $this->getSource()->getShippingIgstAmount(),
                    'label' => __('Shipping IGST'),
                ]);
            }
        }

        return parent::_initTotals();
    }
}
