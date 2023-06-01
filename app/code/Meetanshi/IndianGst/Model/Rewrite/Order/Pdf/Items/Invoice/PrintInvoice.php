<?php

namespace Meetanshi\IndianGst\Model\Rewrite\Order\Pdf\Items\Invoice;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice as CoreInvoice;

class PrintInvoice extends CoreInvoice
{
    public function draw()
    {
        $orderData = $this->getOrder();
        $itemData = $this->getItem();
        $pdfData = $this->getPdf();
        $pageData = $this->getPage();
        $linesArray = [];
        $fontSize = 8;
        $hsnCode = $this->getHsnValue($itemData);

        $itemTotal = $itemData->getRowTotal()  - $itemData->getDiscountAmount();
        $subTotal = $itemData->getRowTotal();
        if ($itemData->getGstExclusive()) {
            $taxableAmount = $itemData->getRowTotal();
        } else {
            $taxableAmount = round($itemData->getRowTotal(), 2) -
                round($itemData->getCgstAmount() ? $itemData->getCgstAmount(): 0, 2) -
                round($itemData->getSgstAmount() ? $itemData->getSgstAmount() : 0, 2) -
                round($itemData->getIgstAmount() ? $itemData->getIgstAmount() : 0, 2) -
                round($itemData->getUtgstAmount() ? $itemData->getUtgstAmount() : 0, 2) - $itemData->getDiscountAmount();
        }

        $productName = $this->string->split($itemData->getName(), 20);
        $productName[] = $orderData->formatPriceTxt($itemData->getPrice()) . ' X ' . $itemData->getQty() * 1;
        $productName[] = "SKU: " . $itemData->getSku();
        $productName[] = "HSN: " . $hsnCode;
        $removeGap = 30;
        $linesArray[0] = [
            [
                'text' => $productName,
                'feed' => 30,
                'font' => 'bold',
                'font_size' => $fontSize
            ]
        ];

        $linesArray[0][] = [
            'text' => $orderData->formatPriceTxt($subTotal),
            'feed' => 195 - $removeGap,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];

        $linesArray[0][] = [
            'text' => $orderData->formatPriceTxt($itemData->getDiscountAmount()),
            'feed' => 240 - $removeGap,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];

        $linesArray[0][] = [
            'text' => $orderData->formatPriceTxt($taxableAmount),
            'feed' => 305 - $removeGap,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];

        $cgst = $this->string->split($orderData->formatPriceTxt($itemData->getCgstAmount()), 10);
        $cgst[] = "Rate:" . floatval($itemData->getCgstPercent()) . "%";
        $linesArray[0][] = [
            'text' => $cgst,
            'feed' => 357 - $removeGap,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];


        if ($itemData->getSgstPercent() > 0) {
            $sgstUtgstPercent = $itemData->getSgstPercent();
            $sgstUtgstAmount = $itemData->getSgstAmount();
        } else {
            $sgstUtgstPercent = $itemData->getUtgstPercent();
            $sgstUtgstAmount = $itemData->getUtgstAmount();
        }

        $sgstUtgst = $this->string->split($orderData->formatPriceTxt($sgstUtgstAmount), 10);
        $sgstUtgst[] = "Rate:" . floatval($sgstUtgstPercent) . "%";
        $linesArray[0][] = [
            'text' => $sgstUtgst,
            'feed' => 417,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];

        $igst = $this->string->split($orderData->formatPriceTxt($itemData->getIgstAmount()), 10);
        $igst[] = "Rate:" . floatval($itemData->getIgstPercent()) . "%";
        $linesArray[0][] = [
            'text' => $igst,
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];

        $linesArray[0][] = [
            'text' => $orderData->formatPriceTxt($itemTotal),
            'feed' => 565,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];

        $options = $this->getItemOptions();

        if ($options) {
            foreach ($options as $option) {
                $linesArray[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 30, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                    'font_size' => $fontSize
                ];

                if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $linesArray[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 35];
                    }
                }
            }
        }

        $lineBlock = ['lines' => $linesArray, 'height' => 20];

        $pageData = $pdfData->drawLineBlocks($pageData, [$lineBlock], ['table_header' => true]);
        $this->setPage($pageData);
    }

    private function getHsnValue($itemData)
    {
        $objectManager = ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($itemData->getProductId());

        if ($product->getHsnCode()) {
            return $product->getHsnCode();
        } else {
            return 'N/A';
        }
    }
}
