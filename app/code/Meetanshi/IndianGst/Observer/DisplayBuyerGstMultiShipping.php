<?php


namespace Meetanshi\IndianGst\Observer;


use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use \Magento\Quote\Model\Quote\Address as QuoteAddressModelFactory;
use \Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResourceModel;
use Meetanshi\IndianGst\Helper\Data;

class DisplayBuyerGstMultiShipping
{

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var QuoteAddressModelFactory
     */
    private $quoteAddressModelFactory;
    /**
     * @var QuoteAddressResourceModel
     */
    private $quoteAddressResourceModelFactory;
    /**
     * @var Multishipping
     */
    private $multishipping;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper,
        Multishipping $multishipping,
        QuoteAddressModelFactory $quoteAddressModelFactory,
        QuoteAddressResourceModel $quoteAddressResourceModelFactory,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->addressRepository = $addressRepository;
        $this->quoteAddressModelFactory = $quoteAddressModelFactory;
        $this->quoteAddressResourceModelFactory = $quoteAddressResourceModelFactory;
        $this->multishipping = $multishipping;
        $this->helper = $helper;
    }

    public function afterExecute($sourceClass)
    {
        try {
            if ($this->helper->isEnabled()) {
                $address = $this->multishipping->getQuote()->getShippingAddress();
                $this->setBuyerGstNumberInQuoteAddress($address->getId());
                $address = $this->multishipping->getQuote()->getBillingAddress();
                $this->setBuyerGstNumberInQuoteAddress($address->getId());
            }
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
        }
    }

    public function setBuyerGstNumberInQuoteAddress($quoteAddressId)
    {
        try {
            $quoteAddressModel = $this->quoteAddressModelFactory;
            $quoteAddressResourceModel = $this->quoteAddressResourceModelFactory;
            $quoteAddressResourceModel->load($quoteAddressModel, $quoteAddressId);
            $customerAddressId = $quoteAddressModel->getCustomerAddressId();
            if ($customerAddressId != 0) {
                $addressObject = $this->addressRepository->getById($customerAddressId);
                $buyerGst = $addressObject->getCustomAttribute('buyer_gst_number');
                if ($buyerGst) {
                    $buyerGstValue = $buyerGst->getValue();
                    $quoteAddressModel->setBuyerGstNumber($buyerGstValue);
                    $quoteAddressResourceModel->save($quoteAddressModel);
                    return $buyerGstValue;
                }
            }

        } catch (AlreadyExistsException $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
        } catch (LocalizedException $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
        }
        return false;
    }

}
