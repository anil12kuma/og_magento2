<?php
namespace Mega\Phonelogin\Helper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;

class CustomerHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

	protected $address;

	protected $addressFactory;

	protected $storeManager;

	protected $addressRepositoryInterface;

	protected $pageFactory;
    protected $customerFactory;

	public function __construct(
		\Magento\Customer\Model\Address $address,
		\Magento\Customer\Model\ResourceModel\AddressFactory $addressFactory,
		\Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
		StoreManagerInterface $storeManager,
		CustomerFactory $customerFactory
	)
	{
		$this->addressRepositoryInterface = $addressRepositoryInterface;
		$this->address = $address;
		$this->addressFactory = $addressFactory;
		$this->storeManager = $storeManager;
		$this->customerFactory = $customerFactory;
	}

	public function saveMobileVerified($addressId,$attributevalue)
	{
		try{
			$address = $this->addressRepositoryInterface->getById($addressId);
			if($address && $address->getId()) {
			    $address->setCustomAttribute('is_mobile_verified', $attributevalue); 
			    $this->addressRepositoryInterface->save($address);
			    return true;
			}
		} catch (Exception $e) {
			return false;
		}



		// $customer = $this->address->load($customerId);
		// $customerData = $customer->getDataModel();
		// $customerData->setCustomAttribute($attributeCode,$attributevalue);
		// $customer->updateData($customerData);
		// $customerResource = $this->addressFactory->create();
		// $customerResource->saveAttribute($customer,$attributeCode);
	}

	// public function getMobileVerified($customerId)
	// {
	// 	try {
	// 		$attributeCode = "is_mobile_verified";
	// 		$customer =$this->addressRepositoryInterface->getById($customerId);
	// 		if($customer->getCustomAttribute($attributeCode) !== null){
	// 			$value = $customer->getCustomAttribute($attributeCode)->getValue();
	// 			 return $value;
	// 		}
	// 		return 0;
	// 	} catch (Exception $e) {
	// 		return 0;
	// 	}
	// }


	public function checkVerifiedNumber($customerId, $mobileNumber){
		$address = $this->getCustomerAddress($customerId);
		$this->createLog('customer_id_p',$customerId);
		$this->createLog('teli_p',$mobileNumber);

		foreach($address as $add){
			if(($add['telephone'] == $mobileNumber) && ($add['mobile_verified'] == 1)){
				$this->createLog('customer_id',$customerId);
				$this->createLog('already_verified_mobile_number',$add['telephone'] );
				return true;
			}
		}
		return false;

	}

	public function getCustomerAddress($customerId)
    {
	    $customer = $this->customerFactory->create();
	    $websiteId = $this->storeManager->getStore()->getWebsiteId();
	    $customer->setWebsiteId($websiteId);
	    $customerModel = $customer->load($customerId);
	    $customerAddressData = [];
	    $customerAddress = [];

	    if ($customerModel->getAddresses() != null)
	    {
	      foreach ($customerModel->getAddresses() as $address) {
	      		$customerAddress['telephone'] = $address['telephone'];
	      		$customerAddress['mobile_verified'] = $address->getData('is_mobile_verified');
	      		$customerAddressData[] = $customerAddress;
	      }
	    }
	    $this->createLog('data',json_encode($customerAddressData));
	    return $customerAddressData;
    }

    public function createLog($label,$value){
    	$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/phonelogin_address_log.log');
	    $logger = new \Zend_Log();
	    $logger->addWriter($writer);
	    $logger->info($label);
		$logger->info($value);
    }

}