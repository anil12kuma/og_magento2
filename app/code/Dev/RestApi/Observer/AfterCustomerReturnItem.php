<?php

namespace Dev\RestApi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class AfterCustomerReturnItem implements ObserverInterface
{
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;

    protected $logger;

     public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\Customer $customer,
        LoggerInterface $logger
    )
    {
        $this->order = $order;
        $this->logger = $logger;
        $this->customer = $customer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->get('\Amasty\Rma\Block\Returns\NewReturn');
        $resolutions = $block->getResolutions();
        
        $order = $this->order->load($request->getOrderId());
        $order_incr_id = $order->getIncrementId();
        $requestItem = $request->getRequestItems();

        foreach ($requestItem as $req) {
           $resolutionId = $req->getResolutionId(); 
        }

        foreach ($resolutions as $resolution) {
            if ($resolution->getData('resolution_id') == $resolutionId) {
                $resolutionLabel = $resolution->getData('label');
                break;
            }
        }

        $customerId = $request->getCustomerId(); // using this id you can get customer name
        $customerData = $this->customer->load($customerId);
        $email = $customerData->getEmail();

        if ($resolutionLabel == 'Return') {
            $type  = "MAGE_ORDER_RETURN";
        }elseif ($resolutionLabel == 'Exchange') {
            $type  = "MAGE_ORDER_EXCHANGE";
        }
        
        $this->logger->info("Order : $order_incr_id");
        $this->logger->info("Email : $email");
        $this->logger->info("Type  : $type");

        $curl = curl_init();

        $params = array(
            'order_id' => $order_incr_id,
            'email_id' => $email,
            'type' => $type
        );
        $params = json_encode($params);

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://development.wildcountry.in/wp-json/wcy/v4/notification/mage-trigger',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$params,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $this->logger->info(print_r($response, true));
        curl_close($curl);
    }
}