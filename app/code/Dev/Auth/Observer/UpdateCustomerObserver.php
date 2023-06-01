<?php
namespace Dev\Auth\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RegisterSuccessObserver implements ObserverInterface
{

	public function __construct(){
		// $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute(Observer $observer)
    {
    	// echo 'observer';exit;
	 	// $customer = $observer->getCustomer();
	 	$customer = $observer->getEvent()->getCustomer();
	 	// print_r($customer);exit;
    }
}