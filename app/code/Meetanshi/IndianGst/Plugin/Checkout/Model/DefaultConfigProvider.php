<?php
namespace Meetanshi\IndianGst\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Meetanshi\IndianGst\Helper\Data as HelperData;

class DefaultConfigProvider
{
    protected $checkoutSession;
    protected $helper;

    public function __construct(
        CheckoutSession $checkoutSession,
        HelperData $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    public function aroundGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        \Closure $proceed
    ) {
        $result = $proceed();

        if (isset($result['quoteItemData'])) {
            $result['quoteItemData']['originState'] = $this->helper->getOrigin();
            $result['quoteItemData']['unionTerritory']= true;

            return $result;
        }
    }
}
