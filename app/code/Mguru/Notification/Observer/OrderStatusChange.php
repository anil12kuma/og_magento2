<?php
namespace Mguru\Notification\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Controller\ResultFactory;

 
class OrderStatusChange implements ObserverInterface
{	
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransportBuilder $transportBuilder,
        array $data = [])
    {
        $this->transportBuilder = $transportBuilder;
    }

	public function execute(\Magento\Framework\Event\Observer $observer)
    {	

		$order = $observer->getEvent()->getOrder();
		$orderStatus = $order->getStatus();
		$orderId = $order->getId();
		$ordid = $order->getIncrementId();
		$grandTotal = $order->getGrandTotal();
		$firstName = $order->getBillingAddress()->getFirstname();
		$customerTelephone = $order->getBillingAddress()->getTelephone();
		$customerEmail = $order->getCustomerEmail();

		$orderItems = $order->getAllItems();
		$total_qty = 0;
		foreach ($orderItems as $item) {
			if($item->getProductType() == 'simple'){
				$total_qty = $total_qty + $item->getQtyOrdered();
			}
		}

		if($customerTelephone !="") {
			/*if($orderStatus == 'pending') {
				$msg = 'Hey '.$firstName.', your OutdoorGoats order no. '.$ordid.' for '.$total_qty.' item(s) has been successfully placed. Thank you for shopping with us.';
				$path ='http://smpp.whistle.mobi/sendsms.jsp?user=WildCoun&password=67ce7b5bf4XX&senderid=OGOATS&mobiles='.$customerTelephone.'&sms='.urlencode($msg).'';
				$ch = curl_init($path);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_exec($ch);
				curl_close($ch);
			}*/
			
			if($orderStatus == 'processing') {
				$msg = 'Hey '.$firstName.', your OutdoorGoats order no. '.$ordid.' for '.$total_qty.' item(s) has been successfully placed. Thank you for shopping with us.';
				$path ='http://smpp.whistle.mobi/sendsms.jsp?user=WildCoun&password=67ce7b5bf4XX&senderid=OGOATS&mobiles='.$customerTelephone.'&sms='.urlencode($msg).'';
				$ch = curl_init($path);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_exec($ch);
				curl_close($ch);
			}
			
			if($orderStatus == 'complete') {
				$title = ''; $trackingNumber = '';
				foreach ($order->getTracksCollection() as $track){
					$trackingNumber = $track->getNumber();
					//$title = $track->getNumber();
					$title = $track->getTitle();
				}

				if($title != '' && $trackingNumber != ''){
					$msg = 'Thank you for shopping at OutdoorGoats. Your order no. '.$ordid.' for INR '.number_format($grandTotal,2).' is shipped through '.$title.'. Your AWB number is '.$trackingNumber;
				}else{
					$msg = 'Hey '.$firstName.', your OutdoorGoats order no. '.$ordid.' for '.$total_qty.' item(s) has been successfully delivered. Thank you for shopping with us';
				}
				
				$path ='http://smpp.whistle.mobi/sendsms.jsp?user=WildCoun&password=67ce7b5bf4XX&senderid=OGOATS&mobiles='.$customerTelephone.'&sms='.urlencode($msg).'';
				$ch = curl_init($path);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_exec($ch);
				curl_close($ch);
			}
			
			if($orderStatus == 'closed') {
				$msg='Dear User, amount for your Order ID:'.$ordid.' has been refunded.';
				$path ='http://smpp.whistle.mobi/sendsms.jsp?user=WildCoun&password=67ce7b5bf4XX&senderid=OGOATS&mobiles='.$customerTelephone.'&sms='.urlencode($msg).'';
				$ch = curl_init($path);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_exec($ch);
				curl_close($ch);
			}

			if($orderStatus == 'canceled') {
				$msg = 'Hey '.$firstName.', your OutdoorGoats order no. '.$ordid.' has been cancelled. You can email us on orders@outdoorgoats.com for any other query.';
				$path ='http://smpp.whistle.mobi/sendsms.jsp?user=WildCoun&password=67ce7b5bf4XX&senderid=OGOATS&mobiles='.$customerTelephone.'&sms='.urlencode($msg).'';
				$ch = curl_init($path);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_exec($ch);
				curl_close($ch);
			}

			//Send Customer cancel ORder Email..
			// if($orderStatus == 'canceled') {
			// 	$emailTemplateVariables = array();
            //     $emailTempVariables['firstName'] = $firstName;
            //     $emailTempVariables['ordid'] = $ordid;

            //     $senderName = 'Columbia Care';

            //     $senderEmail = 'columbiacare@chogoriindia.com';

            //     $postObject = new \Magento\Framework\DataObject();
            //     $postObject->setData($emailTempVariables);

            //     $sender = [
            //                 'name' => $senderName,
            //                 'email' => $senderEmail,
            //                 ];

            //     $transport = $this->transportBuilder->setTemplateIdentifier('cancel_email_template')
            //     ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            //     ->setTemplateVars(['data' => $postObject])
            //     ->setFrom($sender)
            //     // ->addTo($customerEmail)           
            //     ->getTransport();               
            //     $transport->sendMessage();
			// }
		}
		

    }
}