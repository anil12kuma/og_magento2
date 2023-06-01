<?php
namespace Meetanshi\IndianGst\Model\Order\Creditmemo\Total;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal as CreditmemoAbstractTotal;
use Meetanshi\IndianGst\Helper\Data as HelperData;

class ShippingCgst extends CreditmemoAbstractTotal
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
                $gstamount = $creditmemo->getOrder()->getShippingCgstAmount();
                if ($updatedShipAmount > 0) {
                    $amount = ($updatedShipAmount * $gstamount) / ($shipAmount);
                    $creditmemo->setShippingCgstAmount($amount);
                }
            }
        }

        if ($this->helper->getShippingGstClass()) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getShippingCgstAmount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getShippingCgstAmount());
        } else {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal());
        }
        return $this;
    }
}
