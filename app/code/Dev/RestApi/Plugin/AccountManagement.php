<?php

namespace Dev\RestApi\Plugin;

use Magento\Customer\Model\AccountManagement as BaseAccountManagement;
use Psr\Log\LoggerInterface;

class AccountManagement
{
    protected $logger;
    protected $customer;

    public function __construct(\Magento\Customer\Model\Customer $customer, LoggerInterface $logger)
    {
        $this->customer = $customer;
        $this->logger = $logger;
    }

    public function beforeResetPassword(BaseAccountManagement $accountManagement, $email, $resetToken, $newPassword)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $customerData = $this->getCustomerByToken($resetToken, $objectManager);
        $email = $customerData->getEmail();
        $customerId = $customerData->getId();
        $this->customer->load($customerId);
    }

    public function afterResetPassword(BaseAccountManagement $accountManagement, $result, $email, $resetToken, $newPassword)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/wp_customer_update.log');
        $log = new \Zend_Log();
        $log->addWriter($writer);

        $log->info("After Reset Password");

        if ($result) {
            if (!$email) {
                $email = $this->customer->getEmail();
                $customerId = $this->customer->getId();
                $password = $newPassword;
                $firstname = $this->customer->getFirstname();
                $lastname = $this->customer->getLastname();
                
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
                    $log->info("$email password has been updated.");
                }else{
                    $log->info("$email password with ID $customerId has not been updated.");
                    $log->info(print_r($response, true));
                }

                curl_close($curl);
                return;
            }
        }
    }

    public function getCustomerByToken($resetPasswordToken, $objectManager)
    {
        $searchCriteriaBuilder = $objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');

        $searchCriteriaBuilder->addFilter(
            'rp_token',
            $resetPasswordToken
        );
        $searchCriteriaBuilder->setPageSize(1);
        $found = $customerRepository->getList(
            $searchCriteriaBuilder->create()
        );

        return $found->getItems()[0];
    }
}