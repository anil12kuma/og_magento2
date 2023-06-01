<?php

namespace Meetanshi\IndianGst\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Template;
use Meetanshi\IndianGst\Helper\Data;

class ShowBuyerGstShipping implements ObserverInterface
{
    protected $template;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper,Template $template)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }
        $this->template = $template;
        $this->helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }
        if ($observer->getElementName() == 'sales.order.info') {
            $orderShippingView = $observer->getLayout()->getBlock($observer->getElementName());
            $order = $orderShippingView->getOrder();
            $buyerGst = __('N/A');
            if ($order->getShippingAddress()->getBuyerGstNumber() != '') {
                $buyerGst = $order->getShippingAddress()->getBuyerGstNumber();
            }

            $this->template->setBuyerGstNumber($buyerGst);
            $html = $observer->getTransport()->getOutput() . $this->template->setTemplate('Meetanshi_IndianGst::customer/buyergst.phtml')->toHtml();
            $observer->getTransport()->setOutput($html);
        }
    }
}
