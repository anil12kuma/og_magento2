<?php
namespace Mguru\Notification\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;

 
class SignupSuccess implements ObserverInterface
{
	public function execute(\Magento\Framework\Event\Observer $observer)
    {	
        $customer = $observer->getEvent()->getCustomer();
		$name = $customer->getFirstName();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerData = $objectManager->create('Magento\Customer\Model\Customer')->load($customer->getId());
        $telephone = $customerData->getData('mobile');
        
        if($telephone !=""){
			$msg = "Dear $name, Thanks for doing registration on Columbia Sportswears.";
			$path = 'http://sms.valueleaf.com/sms/user/urlsms.php?username=ChogoriOTP&pass=nC@3a5!S&senderid=COLUMB&dest_mobileno='.$telephone.'&message='.urlencode($msg).'&response=Y';
			$ch = curl_init($path);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_exec($ch);
			curl_close($ch);						
		}	

    }
}