<?php


namespace Meetanshi\IndianGst\Controller\Index;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Meetanshi\IndianGst\Helper\Data;

class setBillingBuyerGst extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;
    /**
     * @var
     */
    protected $objectManager;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var
     */
    private $helper;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Data $helper,
        Context $context,
        PageFactory $pageFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->pageFactory = $pageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        return parent::__construct($context);
    }

    /**
     * @return bool|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $response->setHeader('Content-type', 'text/plain');
            if (!$this->helper->isEnabled()) {
                return $response;
            }
            $quote = $this->checkoutSession->getQuote();
            $buyerGstNumber = $this->getRequest()->getParam('buyer_gst_number');
            $quote->setBuyerGstNumber($buyerGstNumber);
            $quote->save();
            $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $response->setHeader('Content-type', 'text/plain');
            return $response;
        } catch (\Exception $ex) {
            ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($ex->getMessage());
            $this->messageManager->addErrorMessage(__($ex->getMessage()));
            return false;
        }
    }
}