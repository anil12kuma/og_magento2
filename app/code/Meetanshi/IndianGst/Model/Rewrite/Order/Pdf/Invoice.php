<?php

namespace Meetanshi\IndianGst\Model\Rewrite\Order\Pdf;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Invoice extends AbstractPdf
{
    private $localeResolver;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        Data $paymentData,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Config $pdfConfig,
        Factory $pdfTotalFactory,
        ItemsFactory  $pdfItemsFactory,
        TimezoneInterface $localeDate,
        StateInterface $inlineTranslation,
        Renderer $addressRenderer,
        array $data = [],
        Database $fileStorageDatabase = null
    ) {
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $data
        );
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Draw lines
     *
     * Draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parameter), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param \Zend_Pdf_Page $page
     * @param array $draw
     * @param array $pageSettings
     * @return \Zend_Pdf_Page
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Pdf_Exception
     * @throws LocalizedException
     */
    public function drawLineBlocks(\Zend_Pdf_Page $page, array $draw, array $pageSettings = [])
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                throw new LocalizedException(
                    __('We don\'t recognize the draw line data. Please define the "lines" array.')
                );
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = \Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = [$column['text']];
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $lineSpacingForCalculation = !empty($column['height_for_calculation']) ? $column['height_for_calculation'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        if ($this->y - $lineSpacingForCalculation < 15) {
                            $page = $this->newPage($pageSettings);
                        }

                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                            default:
                                break;
                        }
                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }

    public function afterGetPdf(\Magento\Sales\Model\Order\Pdf\Invoice $invoiceClass, $result, $invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        try {
            $this->_setPdf($pdf);
            $style = new \Zend_Pdf_Style();
            $this->_setFontBold($style, 10);

            foreach ($invoices as $invoice) {
                if ($invoice->getStoreId()) {
                    $this->localeResolver->emulate($invoice->getStoreId());

                    $this->storeManager->setCurrentStore($invoice->getStoreId());
                }
                $page = $this->newPage();
                $order = $invoice->getOrder();
                /* Add image */
                $this->insertLogo($page, $invoice->getStore());
                /* Add address */
                $this->insertAddress($page, $invoice->getStore());
                /* Add head */
                $this->insertOrder(
                    $page,
                    $order,
                    $this->_scopeConfig->isSetFlag(
                        self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $order->getStoreId()
                    )
                );
                /* Add document text and number */
                $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());

                /* Add table */
                $this->_drawHeader($page);
                /* Add body */

                foreach ($invoice->getAllItems() as $item) {
                    if ($item->getOrderItem()->getParentItem()) {
                        continue;
                    }
                    /* Draw item */
                    $this->_drawItem($item, $page, $order);
                    $page = end($pdf->pages);
                }
                /* Add totals */
                $objectManager = ObjectManager::getInstance();
                $storeInformation = $objectManager->create('Magento\Store\Model\Information');
                $storeInfo = $storeInformation->getStoreInformationObject($order->getStore());

                $page = $this->insertTotals($page, $invoice);
                $this->insertDigiSignature($page, $storeInfo);
                if ($invoice->getStoreId()) {
                    $this->localeResolver->revert();
                }
            }
        } catch (\Zend_Pdf_Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('app/code/Meetanshi/IndianGst/lib/DejavuSans/ttf/DejaVuSans.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }

    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */

        try {
            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 15);
            $this->y -= 10;
            $fontSize = 8;
            $removeGap = 30;
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));
            //columns headers
            $taxableAmountText = $this->string->split('Taxable Amount', 8);
            $lines[0][] = [
                'text' => __('Products (Price x Qty)'),
                'feed' => 35,
                'font_size' => $fontSize
            ];
            $lines[0][] = [
                'text' => __('Subtotal'),
                'feed' => 195 - $removeGap,
                'align' => 'right',
                'font_size' => $fontSize
            ];
            $lines[0][] = [
                'text' => __('Discount'),
                'feed' => 248 - $removeGap,
                'align' => 'right',
                'font_size' => $fontSize
            ];
            $lines[0][] = [
                'text' => __('Taxable Amt'),
                'feed' => 305 - $removeGap,
                'align' => 'right',
                'font_size' => $fontSize
            ];
            $lines[0][] = [
                'text' => __('CGST'),
                'feed' => 352 - $removeGap,
                'align' => 'right',
                'font_size' => $fontSize
            ];
            $lines[0][] = [
                'text' => __('SGST / UTGST'),
                'feed' => 417,
                'align' => 'right',
                'font_size' => $fontSize
            ];
            $lines[0][] = [
                'text' => __('IGST'),
                'feed' => 485,
                'align' => 'right',
                'font_size' => $fontSize
            ];
            $lines[0][] = [
                'text' => __('Row Total'),
                'feed' => 565,
                'align' => 'right',
                'font_size' => $fontSize
            ];
            $lineBlock = [
                'lines' => $lines,
                'height' => 5,
                $this->y,
                'font_size' => $fontSize
            ];

            $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $this->y -= 20;
        } catch (LocalizedException $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
    }

    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('app/code/Meetanshi/IndianGst/lib/DejavuSans/ttf/DejaVuSans.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        $order = '';
        if ($obj instanceof Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $objectManager = ObjectManager::getInstance();
        $helper = $objectManager->create('\Meetanshi\IndianGst\Helper\Data');

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 200);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);
        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
        }

        try {
            $page->drawText(
                __('Order Date:: ') .
                $this->_localeDate->formatDate(
                    $this->_localeDate->scopeDate(
                        $order->getStore(),
                        $order->getCreatedAt(),
                        true
                    ),
                    \IntlDateFormatter::MEDIUM,
                    false
                ),
                35,
                $top -= 15,
                'UTF-8'
            );
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceDate = $invoice->getCreatedAt();
            }
            $page->drawText(
                __('Invoice Date:: ') .
                $this->_localeDate->formatDate(
                    $this->_localeDate->scopeDate(
                        $order->getStore(),
                        $invoiceDate,
                        true
                    ),
                    \IntlDateFormatter::MEDIUM,
                    false
                ),
                35,
                $top -= 15,
                'UTF-8'
            );
        } catch (\Zend_Pdf_Exception $e) {
        }

        $page->drawText(__('Tax Invoice/Bill of Supply/Cash Memo'), 325, $top += 50, 'UTF-8');
        $page->drawText(__('GST Number : ') . $helper->getGstNumber(), 325, $top -= 15, 'UTF-8');

        $panNumber = $helper->getPanNumber();
        $top -= 15;
        if (isset($panNumber)):
            $page->drawText(__('PAN Number : ') . $panNumber, 325, $top, 'UTF-8');
        endif;
        $cinNumber = $helper->getCinNumber();
        $top -= 15;
        if (isset($cinNumber)):
            $page->drawText(__('CIN Number : ') . $cinNumber, 325, $top, 'UTF-8');
        endif;
        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($this->addressRenderer->format(
                $order->getShippingAddress(),
                'pdf'
            ));
            $shippingMethod = $order->getShippingDescription();
            $order->getShippingAddress()->getCountryId();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);

            $this->y = $addressesEndY - 10;
            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $storeInformation = $objectManager->create('Magento\Store\Model\Information');
            $storeInfo = $storeInformation->getStoreInformationObject($order->getStore());

            $storeAddress1 = $storeInfo['street_line1'] . ' ' . $storeInfo['street_line2'] . ', ' . $storeInfo['city'] . ', ' . $storeInfo['region'] . ', ' . $storeInfo['country'] . ' - ' . $storeInfo['postcode'];

            $this->y -= 15;
            $this->_setFontBold($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Supplier Address'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Place of Supply'), 285, $this->y, 'UTF-8');

            $gstAddress = $order->getShippingAddress();
            if ($gstAddress == null) {
                $gstAddress = $order->getBillingAddress();
            }
            $regionData = $gstAddress->getRegion();
            $region = $objectManager->create('Magento\Directory\Model\Region')
                ->loadByName(trim($regionData), $gstAddress->getCountryId());
            $regionData = $regionData;
            if (!empty($region->getStateCode())) {
                $regionData = $regionData . ' (' . $region->getStateCode() . ')';
            }
            $this->y -= 25;
            $this->_setFontBold($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $top = $this->y + 5;
            if ($storeAddress1) {
                foreach ($this->string->split($storeAddress1, 45, true, true) as $_value) {
                    $page->drawText(
                        trim(strip_tags($_value)),
                        35,
                        $top,
                        'UTF-8'
                    );
                    $top -= 10;
                }
            }

            $this->_setFontBold($page, 10);
            $page->drawText(__($regionData), 285, $this->y, 'UTF-8');

            $page->drawLine(25, $addressesEndY - 17, 25, $this->y - 22);
            $page->drawLine(570, $addressesEndY - 17, 570, $this->y - 22);
            $page->drawLine(25, $addressesEndY - 72, 570, $this->y - 22);

            $this->y = $this->y - 30;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . __(
                'Total Shipping Charges'
            ) . " " . $order->formatPriceTxt(
                $order->getShippingAmount()
            ) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);


                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            $this->_drawFooter($page);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    protected function _drawFooter(\Zend_Pdf_Page $page)
    {
        /* Add table foot */
        $this->_setFontRegular($page, 10);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));
    }

    protected function insertDigiSignature(&$page, $storeInfo)
    {
        $objectManager = ObjectManager::getInstance();
        $helperData = $objectManager->create('\Meetanshi\IndianGst\Helper\Data');
        $this->y = $this->y ? $this->y : 815;
        $image = $helperData->getSignature();

        if ($image) {
            $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
            $image = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath("indiangst/" . $helperData->getSignature());
            if (is_file($image)) {
                $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath("indiangst/" . $helperData->getSignature());
                $image = \Zend_Pdf_Image::imageWithPath($mediaPath);

                $top = $this->y - 100; //top border of the page
                $widthLimit = 100; //half of the page width
                $heightLimit = 100; //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 450;
                $x2 = $x1 + $width;

                $font = $this->_setFontRegular($page, 10);
                $feed = $this->getAlignCenter(__($helperData->getSignatureText()), $x1, $width, $font, 10);
                $page->drawImage($image, $x1, $y1 + 100, $x2, $y2 + 100);
                $page->drawText(__($helperData->getSignatureText()), $feed, $this->y - 40, 'UTF-8');
                $page->drawText(__('Authorized Signatory'), $x1, $this->y - 60, 'UTF-8');

                $this->y = $y1 - 5;
            }
        }
    }

    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('app/code/Meetanshi/IndianGst/lib/DejavuSans/ttf/DejaVuSans.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Retrieve PDF
     *
     * @return \Zend_Pdf
     */
    public function getPdf()
    {
    }
}
