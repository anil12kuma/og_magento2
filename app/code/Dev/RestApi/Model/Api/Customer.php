<?php
namespace Dev\RestApi\Model\Api;

use Dev\RestApi\Api\CustomerInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Customer\Model\CustomerRegistry;
use \Magento\Framework\Encryption\EncryptorInterface;

class Customer implements CustomerInterface
{

	/**
     * @var CustomerRepositoryInterface
     */
    private $_customerRepositoryInterface;

    /**
     * @var CustomerRegistry
     */
    private $_customerRegistry;

    /**
     * @var EncryptorInterface
     */
    private $_encryptor;


	public function __construct(CustomerRepositoryInterface $customerRepositoryInterface, CustomerRegistry $customerRegistry, EncryptorInterface $encryptor){
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->_customerRegistry = $customerRegistry;
		$this->_encryptor = $encryptor;

	}

	public function changePassword(string $customerId, string $password){
		$ciphering = "AES-128-CTR";
        $options = 0;
        $decryption_iv = '1234567891011121';
  
        // Store the decryption key
        $decryption_key = "wildcountry";

        // Use openssl_decrypt() function to decrypt the data
        $decryption_customerId = openssl_decrypt($customerId, $ciphering, $decryption_key, $options, $decryption_iv);
        $decryption_password = openssl_decrypt($password, $ciphering, $decryption_key, $options, $decryption_iv);

		$customer = $this->_customerRepositoryInterface->getById($decryption_customerId); // _customerRepositoryInterface is an instance of \Magento\Customer\Api\CustomerRepositoryInterface
		$customerSecure = $this->_customerRegistry->retrieveSecureData($decryption_customerId); // _customerRegistry is an instance of \Magento\Customer\Model\CustomerRegistry
		$customerSecure->setRpToken(null);
		$customerSecure->setRpTokenCreatedAt(null);
		$customerSecure->setPasswordHash($this->_encryptor->getHash($decryption_password, true)); // here _encryptor is an instance of \Magento\Framework\Encryption\EncryptorInterface
		$this->_customerRepositoryInterface->save($customer);

		return array(array('message' => 'Password changed'));
	}

    public function createCustomer($email, $firstname, $lastname, $password)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$customerFactory = $objectManager->create('\Magento\Customer\Model\CustomerFactory');
		$store = $storeManager->getStore();
        $storeId = $store->getStoreId();
		
		$websiteId = $storeManager->getStore($storeId)->getWebsiteId();
        $customer = $customerFactory->create();

		$customer->setWebsiteId($websiteId)
				 ->setStore($store)
				 ->setFirstname($firstname)
				 ->setLastname($lastname)
				 ->setEmail($email)
				 ->setPassword($password);

		$customer->save();

		return [["customer_id" => $customer->getId()]];
	}
}