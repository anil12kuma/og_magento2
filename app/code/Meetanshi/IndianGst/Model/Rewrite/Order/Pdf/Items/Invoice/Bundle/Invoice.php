<?php

namespace Meetanshi\IndianGst\Model\Rewrite\Order\Pdf\Items\Invoice\Bundle;

use Magento\Bundle\Model\Sales\Order\Pdf\Items\Invoice as CoreInvoice;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Invoice\Item;
use Psr\Log\LoggerInterface;

class Invoice extends CoreInvoice
{
    public function draw()
    {
        try {
            $orderData = $this->getOrder();
            /**
             * @var $itemData Item
             */
            $itemData = $this->getItem();
            $pdfData = $this->getPdf();
            $pageData = $this->getPage();
            $removeGap = 30;
            $this->_setFontRegular();
            $orderItems = $this->getChildren($itemData);
            $prevOptionDataId = '';
            $drawItemsData = [];
            $fontSize = 8;
            $lineArray = [];
            $mainProductName=$itemData->getName();
            $mainProductHsn=$this->getHsnValue($itemData);

            $productNameHsn = $this->string->split($mainProductName, 20, true, true);
            $productNameHsn[] = $orderData->formatPriceTxt($itemData->getPrice()) . ' X ' . $itemData->getQty() * 1;
            $productNameHsn[] = "SKU: " . $itemData->getSku();
            $productNameHsn[] = "HSN: " . $mainProductHsn;
            $productNameHsn[] = '';
            $lineArray[] = ['text' => $productNameHsn, 'feed' => 40, 'font' => 'bold', 'font_size' => $fontSize];
            $drawItemsData[0] = ['lines' => [$lineArray], 'height' => 15];

            foreach ($orderItems as $orderItem) {
                /**
                 * @var $orderItem Item
                 */
                $lineArray = [];
                $hsnCode = $this->getHsnValue($orderItem);


                $itemAttributes = $this->getSelectionAttributes($orderItem);
                if (is_array($itemAttributes)) {
                    $itemOptionId = $itemAttributes['option_id'];
                } else {
                    $itemOptionId = 0;
                }

                if (!isset($drawItemsData[$itemOptionId])) {
                    $drawItemsData[$itemOptionId] = ['lines' => [], 'height' => 15];
                }

                if ($orderItem->getOrderItem()->getParentItem()) {
                    if ($prevOptionDataId != $itemAttributes['option_id']) {
                        $lineArray[0] = [
                            'font' => 'italic',
                            'text' => $this->string->split($itemAttributes['option_label'], 45, true, true),
                            'feed' => 35,
                            'font_size' => $fontSize
                        ];

                        $drawItemsData[$itemOptionId] = ['lines' => [$lineArray], 'height' => 15];
                        $lineArray = [];

                        $prevOptionDataId = $itemAttributes['option_id'];
                    }
                }

                if ($this->canShowPriceInfo($orderItem)) {
                    $productName = $this->string->split($orderItem->getName(), 16, true, true);
                    $productName[] = $orderData->formatPriceTxt($orderItem->getPrice()) . ' X ' . $orderItem->getQty() * 1;
                    $productName[] = "SKU: " . $orderItem->getSku();
                    $productName[] = "HSN: " . $hsnCode;
                    $productName[] = '';


                    $itemTotal = $orderItem->getRowTotal();
                    $subTotal = $orderItem->getRowTotal() + $itemData->getDiscountAmount();
                    if ($orderItem->getGstExclusive()) {
                        $taxableAmount = $orderItem->getRowTotal();
                    } else {
                        $taxableAmount = round($orderItem->getRowTotal(), 2) -
                            round($orderItem->getCgstAmount() ? $orderItem->getCgstAmount() : 0, 2) -
                            round($orderItem->getSgstAmount() ? $orderItem->getSgstAmount() : 0, 2) -
                            round($orderItem->getIgstAmount() ? $orderItem->getIgstAmount() : 0, 2) -
                            round($orderItem->getUtgstAmount() ? $orderItem->getUtgstAmount(): 0, 2) - $orderItem->getDiscountAmount();
                    }

                    $lineArray[] = ['text' => $productName, 'feed' => 40, 'font' => 'bold', 'font_size' => $fontSize,'height_for_calculation' => sizeof($productName)*10];

                    $subTotal = $orderData->formatPriceTxt($subTotal);
                    $lineArray[] = [
                        'text' => $subTotal,
                        'feed' => 195 - $removeGap,
                        'font' => 'bold',
                        'align' => 'right',
                        'font_size' => $fontSize
                    ];

                    $discount = $orderData->formatPriceTxt($orderItem->getDiscountAmount());
                    $lineArray[] = [
                        'text' => $discount,
                        'feed' => 248 - $removeGap,
                        'font' => 'bold',
                        'align' => 'right',
                        'font_size' => $fontSize
                    ];

                    $tax = $orderData->formatPriceTxt($taxableAmount);
                    $lineArray[] = [
                        'text' => $tax,
                        'feed' => 305 - $removeGap,
                        'font' => 'bold',
                        'align' => 'right',
                        'font_size' => $fontSize
                    ];

                    $cgst = $this->string->split($orderData->formatPriceTxt($orderItem->getCgstAmount()), 10);
                    $cgst[] = "Rate:" . floatval($orderItem->getCgstPercent()) . "%";
                    $lineArray[] = [
                        'text' => $cgst,
                        'feed' => 352 - $removeGap,
                        'font' => 'bold',
                        'align' => 'right',
                        'font_size' => $fontSize
                    ];
                    if ($itemData->getSgstPercent() > 0) {
                        $sgstUtgstPercent = $orderItem->getSgstPercent();
                        $sgstUtgstAmount = $orderItem->getSgstAmount();
                    } else {
                        $sgstUtgstPercent = $orderItem->getUtgstPercent();
                        $sgstUtgstAmount = $orderItem->getUtgstAmount();
                    }

                    $sgstUtgst = $this->string->split($orderData->formatPriceTxt($sgstUtgstAmount), 10);
                    $sgstUtgst[] = "Rate:" . floatval($sgstUtgstPercent) . "%";
                    $lineArray[] = [
                        'text' => $sgstUtgst,
                        'feed' => 410,
                        'font' => 'bold',
                        'align' => 'right',
                        'font_size' => $fontSize
                    ];

                    $igst = $this->string->split($orderData->formatPriceTxt($orderItem->getIgstAmount()), 10);
                    $igst[] = "Rate:" . floatval($orderItem->getIgstPercent()) . "%";
                    $lineArray[] = [
                        'text' => $igst,
                        'feed' => 485,
                        'font' => 'bold',
                        'align' => 'right',
                        'font_size' => $fontSize
                    ];

                    $row_total = $orderData->formatPriceTxt($itemTotal);
                    $lineArray[] = [
                        'text' => $row_total,
                        'feed' => 565,
                        'font' => 'bold',
                        'align' => 'right',
                        'font_size' => $fontSize,
                        'height' => sizeof($productName)*10
                    ];
                }

                $drawItemsData[$itemOptionId]['lines'][] = $lineArray;
            }
            $optionsData = $itemData->getOrderItem()->getProductOptions();
            if ($optionsData) {
                if (isset($optionsData['options'])) {
                    foreach ($optionsData['options'] as $option) {
                        $lines = [];
                        $lines[][] = [
                            'text' => $this->string->split(
                                $this->filterManager->stripTags($option['label']),
                                40,
                                true,
                                true
                            ),
                            'font' => 'italic',
                            'feed' => 35,
                        ];

                        if ($option['value']) {
                            $text = [];
                            $printValue = isset(
                                $option['print_value']
                            ) ? $option['print_value'] : $this->filterManager->stripTags(
                                $option['value']
                            );
                            $values = explode(', ', $printValue);
                            foreach ($values as $value) {
                                foreach ($this->string->split($value, 30, true, true) as $subValue) {
                                    $text[] = $subValue;
                                }
                            }

                            $lines[][] = ['text' => $text, 'feed' => 40];
                        }

                        $drawItemsData[] = ['lines' => $lines, 'height' => 15];
                    }
                }
            }
            $pageData = $pdfData->drawLineBlocks($pageData, $drawItemsData, ['table_header' => true]);

            $this->setPage($pageData);
        } catch (LocalizedException $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
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
