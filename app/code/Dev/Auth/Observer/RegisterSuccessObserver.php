<?php
namespace Dev\Auth\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class RegisterSuccessObserver implements ObserverInterface
{
	protected $_customerRepositoryInterface;
	protected $logger;
	const CUSTOMER_GROUP_ID = 2;

	public function __construct(CustomerRepositoryInterface $customerRepositoryInterface, LoggerInterface $logger)
	{
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->logger = $logger;
	}


	public function execute(Observer $observer)
	{
		// $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/wp_customer_update.log');
		//    $log = new \Zend_Log();
		//    $log->addWriter($writer);
		//    $log->info('Event trigger for Social signup');
		$this->logger->info('Event triggered for create account');
		$customer = $observer->getEvent()->getCustomer();
		// print_r($customer->getId());exit;
		$url = 'http://auth.development.wildcountry.in/sign-up-custom';
		$params = array(
			'magento_user_id' => $customer->getId(),
			'email' => $customer->getEmail(),
			'password' => 'test123$',
			'username' => 'H9h2vPfsK7JMA',
			'role' => 'author',
			'first_name' => $customer->getFirstname(),
			'last_name' => $customer->getLastname(),
		);
		$params = json_encode($params);

		$curl = curl_init();

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 2,
				CURLOPT_NOSIGNAL => 1,
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
		// ignore_user_abort(true);
		// usleep(500000);
		$this->logger->info('Response from auth api');
		$this->logger->info($response);
		curl_close($curl);
		// echo $response;

		return true;

	}

}