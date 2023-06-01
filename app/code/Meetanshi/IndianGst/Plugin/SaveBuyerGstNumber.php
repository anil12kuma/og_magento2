<?php
/**
 * Created by PhpStorm.
 * User: Meetanshi
 * Date: 16-02-2023
 * Time: 18:23
 */

namespace Meetanshi\IndianGst\Plugin;

use Magento\Customer\Controller\Address\FormPost;
use Magento\Framework\Exception\LocalizedException;

class SaveBuyerGstNumber extends FormPost
{
    public function afterExecute(FormPost $subject, $resultRedirectFactory)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/clling.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r($this->getRequest()->getParams(),true));

        try {
            $address = $this->_addressRepository->getById($this->getRequest()->getParam('id'));
            $address->setCustomAttribute('buyer_gst_number', $this->getRequest()->getParam('buyer_gst_number'));
            $this->_addressRepository->save($address);
            $logger->info('save');
            //$logger->info('buyer' . $address->getCustomAttribute('buyer_gst_number')->getValue());
        } catch (LocalizedException $e) {
            $logger->info('errpr' . $e->getMessage());
        }
        return $resultRedirectFactory;
    }
}