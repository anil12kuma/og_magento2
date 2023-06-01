<?php

namespace Meetanshi\IndianGst\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;

class AfterAddressSaveObserver  implements ObserverInterface
{

    /**
 * @var AddressRepositoryInterface
 */
    private $addressRepository;
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        AddressRepositoryInterface $addressRepository,
        RequestInterface $request
    )
    {
        $this->addressRepository = $addressRepository;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/buy.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('$customerAddress->getId() ' . $this->request->getParam('id') );
//
        try
        {
            $address = $this->addressRepository->getById($this->request->getParam('id'));
            $address->setCustomAttribute('buyer_gst_number', $this->request->getParam('buyer_gst_number'));
//            $this->addressRepository->save($address);
        }
        catch (\Exception $e) {
            return __('Error in shipping/billing address.');
        }
    }
}