<?php
namespace Meetanshi\IndianGst\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal as CreditmemoAbstractTotal;
use Magento\Sales\Model\Order\Creditmemo;
use Meetanshi\IndianGst\Helper\Data as HelperData;
use Magento\Framework\App\RequestInterface;

class ShippingIgst extends CreditmemoAbstractTotal
{
    protected $helper;
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(HelperData $helper, RequestInterface $request, array $data = [])
    {
        parent::__construct($data);
        $this->helper = $helper;
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
                $gstamount = $creditmemo->getOrder()->getShippingIgstAmount();
                if ($updatedShipAmount > 0) {
                    $amount = ($updatedShipAmount * $gstamount) / ($shipAmount);
                    $creditmemo->setShippingIgstAmount($amount);
                }
            }
        }

        if ($this->helper->getShippingGstClass()) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getShippingIgstAmount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getShippingIgstAmount());
        } else {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal());
        }
        return $this;
    }
}
