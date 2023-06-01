<?php

namespace Meetanshi\IndianGst\Model\Sales\Pdf;

use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;

class Cgst extends DefaultTotal
{
    protected $helper;

    public function __construct(
        PricingHelper $helper,
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    public function getTotalsForDisplay()
    {
        $orderData = $this->getOrder();
        $amount = $this->getSource()->getCgstAmount();

        $title = __($this->getTitle());
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $orderData->formatPriceTxt($amount),
            'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }
}
