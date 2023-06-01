<?php
namespace Dev\Auth\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\CustomerRegistry;
use Dev\Auth\Helper\PasswordHash;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerPasswordObserver implements ObserverInterface
{
    protected $encryptor;
    protected $WpPasswordHasher;
    protected $customerRegistry;
    protected $customerRepository;
    protected $customerRepositoryInterface;
    protected $logger;

    public function __construct(EncryptorInterface $encryptor,CustomerRegistry $customerRegistry,CustomerRepository $customerRepository, PasswordHash $WpPasswordHasher, LoggerInterface $logger, CustomerRepositoryInterface $customerRepositoryInterface){
        $this->encryptor = $encryptor;
        $this->WpPasswordHasher = $WpPasswordHasher;
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;

        $this->logger = $logger;
    }

    /**
     * Below is the method that will fire whenever the event runs!
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/wp_customer_update.log');
        $log = new \Zend_Log();
        $log->addWriter($writer);

        $requestParams=$observer->getEvent()->getData('request')->getParams();
        $username = $requestParams['login']['username'];
        $password = $requestParams['login']['password'];

        try{
            $customer = $this->customerRepository->get($username);

             $customer_ = $this->customerRepositoryInterface->getById($customer->getId());
        $sub = $customer->getCustomAttribute('sub');

        $log->info("sub of customer is = $sub");


            // print_r($customer->__toArray());exit;
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerdata = $this->customerRegistry->retrieve($customer->getId());
            $hash = $customerSecure->getPasswordHash();
            
            
            

            if($customerdata->getData('is_active') == 0 && $this->WpPasswordHasher->CheckPassword($password, $hash)){
                $customerSecure->setPasswordHash($this->encryptor->getHash($password, true));
                $this->customerRepository->save($customer);

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('customer_entity');
                $sql = "Update " . $tableName . " set is_active = 1 where entity_id = ".$customer->getId();
                $connection->query($sql);

            }
            
            
        }
        catch(\Exception $e){
            // throw $e;
        }
//       try{
//           /** @var \Magento\Customer\Api\Data\CustomerInterface */
//           $customer = $this->customerRepository->get($username);
//           $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
//           $hash = $customerSecure->getPasswordHash();
//           if ($this->WpPasswordHasher->CheckPassword($password, $hash)) {
//               $customerSecure->setPasswordHash($this->encryptor->getHash($password, true));
//               $this->customerRepository->save($customer);
//           }
//       } catch (\Exception $e) {
//       }
    }

}