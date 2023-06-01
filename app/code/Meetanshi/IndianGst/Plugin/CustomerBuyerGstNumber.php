<?php

namespace Meetanshi\IndianGst\Plugin;

use Magento\Framework\App\RequestInterface;
class CustomerBuyerGstNumber
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RequestInterface $request
    )
    {

        $this->request = $request;
    }

    public function afterSave(
        \Magento\Customer\Api\AddressRepositoryInterface $subject,
        $address
    ) {

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/QBMS.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r($address->setCustom,true));
        return $address;
    }
}