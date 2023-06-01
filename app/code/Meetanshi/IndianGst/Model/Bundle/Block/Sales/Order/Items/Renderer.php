<?php

namespace Meetanshi\IndianGst\Model\Bundle\Block\Sales\Order\Items;

use Magento\Bundle\Block\Sales\Order\Items\Renderer as CoreRenderer;

class Renderer extends CoreRenderer
{
    public function getValueHtml($item)
    {
        if ($attributes = $this->getSelectionAttributes($item)) {
            if ($item->getIgstAmount() > 0) {
                $addText = "<br>IGST Percent : ".$this->escapeHtml($item->getIgstPercent())."<br>IGST Amount : ".$this->getOrder()->formatPrice($item->getIgstAmount());
            } elseif ($item->getUtgstAmount() > 0) {
                $addText = "<br>CGST Rate : ".$this->escapeHtml($item->getCgstPercent())."<br>CGST Amount : ".$this->getOrder()->formatPrice($item->getCgstAmount())."<br>UTGST Percent : ".$this->escapeHtml($item->getUtgstPercent())."<br>UTGST Amount : ".$this->getOrder()->formatPrice($item->getUtgstAmount());
            } else {
                $addText = "<br>CGST Rate : ".$this->escapeHtml($item->getCgstPercent())."<br>CGST Amount : ".$this->getOrder()->formatPrice($item->getCgstAmount())."<br>SGST Percent : ".$this->escapeHtml($item->getSgstPercent())."<br>CGST Amount : ".$this->getOrder()->formatPrice($item->getSgstAmount());
            }
            return sprintf('%d', $attributes['qty']) . ' x ' . $this->escapeHtml($item->getName()) . " "
                . $this->getOrder()->formatPrice($attributes['price']) . ' ' . $addText;
        }
        return $this->escapeHtml($item->getName());
    }
}
