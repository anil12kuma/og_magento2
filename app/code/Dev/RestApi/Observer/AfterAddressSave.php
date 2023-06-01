<?php

namespace Dev\RestApi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;

class AfterAddressSave implements ObserverInterface
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

    /**
     * Address after save event handler
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var $customerAddress Address */
        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();

        $this->logger->info('Event trigger Order AfterAddressSaveObserver');
        // $this->logger->info(print_r($customerAddress->toArray(), true));
        // $this->logger->info(print_r($customer->toArray(), true));
        $this->logger->info("phone_no : " . $customerAddress->getTelephone());
        $this->logger->info("default_shipping : " . $customerAddress->getIsDefaultShipping());
        $this->logger->info("default_billing : " . $customerAddress->getIsDefaultBilling());
        $this->logger->info("is_mobile_verified : " . $customerAddress->getIsMobileVerified());
        $this->logger->info("Email : " . $customer->getEmail());

        $email = $customer->getEmail();
        $phone_no = $customerAddress->getTelephone();

        if (!empty($customerAddress->getIsDefaultShipping()) && !empty($customerAddress->getIsDefaultBilling())) {
            $curl = curl_init();

            $params = array(
                'email' => $email,
                'phone_no' => $phone_no,
            );
            $params = json_encode($params);

            curl_setopt_array(
                $curl,
                array(
                CURLOPT_URL => 'http://development.wildcountry.in/wp-json/wcy/v4/update-phone',
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
                )
            );

            $response = curl_exec($curl);
            $this->logger->info(print_r($response, true));
            curl_close($curl);
        }
    }

}