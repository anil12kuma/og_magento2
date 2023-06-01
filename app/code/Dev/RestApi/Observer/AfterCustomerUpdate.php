<?php

namespace Dev\RestApi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class AfterCustomerUpdate implements ObserverInterface
{
    /**
     * Order Model
     *
     * @var \Magento\Customer\Model\Customer $customer
     */
    protected $customer;

    protected $logger;

    protected $request;

     public function __construct(
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\App\RequestInterface $request,
         LoggerInterface $logger
    )
    {
        $this->customer = $customer;
        $this->logger = $logger;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/wp_customer_update.log');
        $log = new \Zend_Log();
        $log->addWriter($writer);

        $customerEmail = $observer->getEvent()->getEmail();
        $websiteId = $storeManager->getStore()->getWebsiteId();
        $customerData = $this->customer->setWebsiteId($websiteId)->loadByEmail($customerEmail);
        $customerId = $customerData->getId();
        
        $log->info('Event trigger for AfterCustomerUpdate');
        $postData = $this->request->getPost();

        $password = $postData['password'];
        $firstname = $postData['firstname'];
        $lastname = $postData['lastname'];
        
        // Store the cipher method
        $ciphering = "AES-128-CTR";
        
        // Use OpenSSl Encryption method
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = 0;
        
        // Non-NULL Initialization Vector for encryption
        $encryption_iv = '1234567891011121';
        
        // Store the encryption key
        $encryption_key = "wildcountry";
        
        // Use openssl_encrypt() function to encrypt the data
        $encryption_password = openssl_encrypt($password, $ciphering, $encryption_key, $options, $encryption_iv);
        $encryption_customerId = openssl_encrypt($customerId, $ciphering, $encryption_key, $options, $encryption_iv);

        $url = 'auth.development.wildcountry.in/update-user';
        $params = array(
            'magento_id' => $encryption_customerId,
            'password' => $encryption_password,
            'firstname' => $firstname,
            'lastname' => $lastname
        );
        $params = json_encode($params);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $params,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response, true);

        if (isset($response['status']) == 'success') {
          $log->info("$customerEmail password has been updated.");
        }else{
          $log->info("$customerEmail password with ID $customerId has not been updated.");
          $log->info(print_r($response, true));
        }
        curl_close($curl);
        return;
    }
}