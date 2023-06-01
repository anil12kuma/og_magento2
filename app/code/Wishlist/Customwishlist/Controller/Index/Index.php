<?php
namespace Wishlist\Customwishlist\Controller\Index;
use Magento\Framework\Controller\ResultFactory;
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_wishlistFactory;
    protected $_wishlistResource;
    protected $customerSession;
    protected $_productRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Model\ResourceModel\Wishlist $wishlistResource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_wishlistFactory  = $wishlistFactory;
        $this->_wishlistResource = $wishlistResource;
        $this->customerSession = $customerSession;
        $this->_productRepository = $productRepository;
        return parent::__construct($context);
    }

    public function execute()
    {
        $product_id = $this->getRequest()->getParam('productid');
        $product = $this->_productRepository->getById($product_id);
        $customerId = $this->customerSession->getCustomer()->getId();
        $this->saveProductToWishlist($product, $customerId);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);        
      
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    public function saveProductToWishlist($product, $customerId)
    {    
       //load wishlist by customer id
       $wishlist = $this->_wishlistFactory->create()->loadByCustomerId($customerId, true);
       //add product for wishlist
       $wishlist->addNewItem($product);
       //save wishlist
       $this->_wishlistResource->save($wishlist);
    }
}
