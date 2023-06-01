<?php
/**
 * Class RestrictAccess
 *
 * PHP version 7.4
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RestrictAccess
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class RestrictAccess implements ObserverInterface
{

	/**
     * Brand Model
     *
     * @var \Sparsh\Brand\Model\Brand
     */
	protected $brand;

	/**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
	protected $customerSession;

	/**
     * StoreManager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $storeManager;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    private $_actionFlag;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * RestrictAccess constructor.
     * @param \Sparsh\Brand\Model\Brand $brand
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
	public function __construct(
		\Sparsh\Brand\Model\Brand $brand,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Http\Context $httpContext
	)
	{
		$this->brand = $brand;
		$this->customerSession = $customerSession;
		$this->storeManager = $storeManager;
		$this->_actionFlag = $actionFlag;
        $this->url = $url;
	}

	/**
	 * Customer Group Restrict Brnad Page Observer Event
	 *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
	public function execute(Observer $observer)
	{
		$request = $observer->getEvent()->getRequest();

		$brandID = $request->getParam('id');
		$brandModel = $this->brand->load($brandID);
		$groupIds = $brandModel->getCustomerGroupId();

		if($this->customerSession->isLoggedIn()){
        	$customerGroupId = $this->customerSession->getCustomer()->getGroupId();
            if(!in_array($customerGroupId, $groupIds))
        	{
                $redirectionUrl = $this->url->getUrl('/');
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $observer->getControllerAction()->getResponse()->setRedirect($redirectionUrl);

                return $this;
        	}
        }
        else
        {
        	if(!in_array(0, $groupIds))
        	{
                $redirectionUrl = $this->url->getUrl('/');
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $observer->getControllerAction()->getResponse()->setRedirect($redirectionUrl);

                return $this;
        	}
        }

	}
}
