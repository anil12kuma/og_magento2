<?php

namespace Meetanshi\IndianGst\Block\Order\Invoice;

use Meetanshi\IndianGst\Block\Order\Totals as OrderTotals;

/**
 * Class Totals
 * @package Meetanshi\IndianGst\Block\Order\Invoice
 */
class Totals extends OrderTotals
{
    /**
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->removeTotal('base_grandtotal');
        return $this;
    }
}
