<?php
namespace Meetanshi\IndianGst\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal as CreditmemoAbstractTotal;
use Magento\Sales\Model\Order\Creditmemo;
use Meetanshi\IndianGst\Helper\Data as HelperData;
use Magento\Framework\App\RequestInterface;

class ShippingUtgst extends CreditmemoAbstractTotal
{
    protected $helper;
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(HelperData $helper, RequestInterface $request, array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($data);
        $this->request = $request;
    }
    public function collect(Creditmemo $creditmemo)
    {

        $params = $this->request->getParams();
        $updatedShipAmount = abs($creditmemo->getOrder()->getShippingInvoiced() - $creditmemo->getOrder()->getShippingRefunded());
        $updatedShipAmount = $updatedShipAmount ?? 0;
        $shipAmount = $creditmemo->getShippingAmount();

        if ($shipAmount > 0) {
            if ($creditmemo->getOrder()->getShippingInvoiced()) {
                if (isset($params['creditmemo']['shipping_amount'])) {
                    $updatedShipAmount = $params['creditmemo']['shipping_amount'];
                }
                $gstamount = $creditmemo->getOrder()->getShippingUtgstAmount();
                if ($updatedShipAmount > 0) {
                    $amount = ($updatedShipAmount * $gstamount) / ($shipAmount);
                    $creditmemo->setShippingUtgstAmount($amount);
                }
            }
        }


        if ($this->helper->getShippingGstClass()) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getShippingUtgstAmount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getShippingUtgstAmount());
        } else {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal());
        }
        return $this;
    }
}
