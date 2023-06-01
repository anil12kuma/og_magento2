<?php
/**
 * @Author      Magetop Team
 * @package     Review Management
 * @copyright   Copyright (c) 2019 MAGETOP (http://www.magetop.com)
 * @terms       http://www.magetop.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
 
namespace Magetop\ReviewManagement\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_PATH_ENABLED          = 'reviewmanagement/general/enabled';
    const XML_PATH_PURCHASE         = 'reviewmanagement/general/purchase';
	const XML_PATH_DEBUG            = 'reviewmanagement/general/debug';

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $_logger;

	/**
	 * @var \Magento\Framework\Module\ModuleListInterface
	 */
	protected $_moduleList;
    protected $_resource;
    protected $_reviewmanagement;
    protected $_objectmanager;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\Module\ModuleListInterface $moduleList
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magetop\ReviewManagement\Model\ReviewManagementFactory $reviewmanagementFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
	) {
		$this->_logger = $context->getLogger();
		$this->_moduleList = $moduleList;
        $this->_resource = $resource;
        $this->_reviewmanagement = $reviewmanagementFactory;
        $this->_objectmanager = $objectmanager;
		parent::__construct($context);
	}

	/**
	 * Check if enabled
	 *
	 * @return string|null
	 */
	public function isEnabled()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_ENABLED,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}
    
    /**
	 * Check if purchased
	 *
	 * @return string|null
	 */
	public function isPurchased()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_PURCHASE,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	public function getDebugStatus()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_DEBUG,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	public function getExtensionVersion()
	{
		$moduleCode = 'Magetop_ReviewManagement';
		$moduleInfo = $this->_moduleList->getOne($moduleCode);
		return $moduleInfo['setup_version'];
	}

	/**
	 *
	 * @param $message
	 * @param bool|false $useSeparator
	 */
	public function log($message, $useSeparator = false)
	{
		if ($this->getDebugStatus()) {
			if ($useSeparator) {
				$this->_logger->addDebug(str_repeat('=', 100));
			}
			$this->_logger->addDebug($message);
		}
	}
    
    public function checkPurchasedProduct($product_id){
        $flag = false;
        $customerSession = $this->_objectmanager->create('Magento\Customer\Model\Session');
		$_dataObject = $customerSession->getCustomerData();
        if(is_object($_dataObject)){
            $coreOrderModel = $this->_objectmanager->create('Magento\Sales\Model\Order')
                                                    ->getCollection()
                                                    ->addAttributeToFilter('customer_id',$_dataObject->getId());
            if(count($coreOrderModel) > 0){
                foreach($coreOrderModel as $order){
                    $orderItems = $order->getAllItems();
                    foreach($orderItems as $od){
                        if($product_id == $od->getProductId()){
                            $flag = true;
                            break;
                        }
                    }
                }
            }
        }
        return $flag;
    }
    
    public function checkLogin(){
        $flag = false;
        $customerSession = $this->_objectmanager->create('Magento\Customer\Model\Session');
		if($customerSession->isLoggedIn()) {
           $flag = true;
        }
        return $flag;
    }

    public function getAtheleteCustomers()
    {
        $connection = $this->_resource->getConnection();
        $sql = "SELECT entity_id from customer_entity where group_id = 4";
        $result = $connection->fetchCol($sql);
        
        $customerIds = array();
        foreach ($result as $key => $value) {
            $customerIds[] = $value;
        }

        return $customerIds;
    }

  public function getCollection($productId)
  {
    //$product = $this->_objectmanager->create("Magento\Catalog\Model\Product")->load($productId);
		$rating = $this->_objectmanager->get("Magento\Review\Model\ResourceModel\Review\CollectionFactory");
		$collection = $rating->create()->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)->addEntityFilter('product',$productId)->setDateOrder();

		return $collection->getItems();
  }

}