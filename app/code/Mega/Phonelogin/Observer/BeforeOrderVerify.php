<?php
namespace Mega\Phonelogin\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class BeforeOrderVerify implements ObserverInterface
{
    protected $messageManager;
    protected $scopeConfig;
    protected $checkoutSession;
    protected $logger;
    protected $_coreSession;
    protected $_customerRepositoryInterface;

    public function __construct(
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectmanager,
        CheckoutSession $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->messageManager               = $messageManager;
        $this->scopeConfig                  = $scopeConfig;
        $this->_objectManager               = $objectmanager;
        $this->checkoutSession              = $checkoutSession;
        $this->logger                       = $logger;
        $this->_coreSession                 = $coreSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;

    }
    public function execute(Observer $observer)
    {

        $order         = $observer->getEvent()->getOrder();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store_id      = $storeManager->getStore()->getStoreId();
        $this->logger->info("storeid==>" . $store_id . "<");

        // get shipping address

        $shippingAddress = $order->getShippingAddress();
        $city            = $shippingAddress->getCity();
        $country         = $shippingAddress->getCountryId();
        $this->logger->info("Sandy=>" . $shippingAddress->getFirstname() . "<");
        $this->logger->info("shippingAddress=>" . $country . "<");
        $this->logger->info("shippingAddress=>" . $city . "<");

    }

}
