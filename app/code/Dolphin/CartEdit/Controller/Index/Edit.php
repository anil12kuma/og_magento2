<?php

namespace Dolphin\CartEdit\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $request;
    protected $cart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->cart = $cart;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $data = $this->request->getParams();
        if (!isset($data['quoteid'])) {
            $this->messageManager->addError(__('Invalid product data.'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        try {
            $quote_data = $this->cart->getQuote()->getItemById($data['quoteid']);

            if (!$quote_data) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the quote item.'));
            }

            $quote_params = $quote_data->getBuyRequest();

            if (isset($data['super_attribute'])) {
                $quote_params->setSuperAttribute($data['super_attribute'][$data['prdid']]);
            } elseif (isset($data['links'])) {
                $quote_params->setLinks($data['links']);
            } elseif ((isset($data['bundle_option'])) && (isset($data['bundle_option_qty']))) {
                $quote_params->setBundleOption($data['bundle_option']);
                $quote_params->setBundleOptionQty($data['bundle_option_qty']);
            } else {
                $quote_params = $quote_data->getBuyRequest();
            }

            if (isset($data['options'])) {
                $quote_params->setOptions($data['options']);
            }

            $quote_params->setQty($data['prdqty']);

            $item = $this->cart->getQuote()->updateItem($data['quoteid'], $quote_params);

            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
            }

            $this->cart->save();
            $this->messageManager->addSuccessMessage(__('Product has been successfully updated.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the item right now.'));
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
