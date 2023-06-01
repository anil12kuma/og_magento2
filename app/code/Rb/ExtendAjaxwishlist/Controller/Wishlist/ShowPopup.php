<?php

namespace Rb\ExtendAjaxwishlist\Controller\Wishlist;

use Tigren\Ajaxwishlist\Controller\Wishlist\ShowPopup as popup;

	class ShowPopup extends popup
	{
	
		public function execute()
		{
			$result = [];
			$params = $this->_request->getParams();
			$isLoggedIn = $this->_ajaxSuiteHelper->getLoggedCustomer();
	
			if ($isLoggedIn == true) {
				try {
					$product = $this->_initProduct();
					if ($product->getTypeId() != "simple"
					&& $product->getTypeId() != "configurable"
						&& $product->getTypeId() != "mageworx_giftcards"
						&& $product->getTypeId() != "downloadable"
						&& $product->getTypeId() != "virtual") {
						$this->_coreRegistry->register('product', $product);
						$this->_coreRegistry->register('current_product', $product);
						$htmlPopup = $this->_ajaxWishlistHelper->getOptionsPopupHtml($product);
						$result['success'] = true;
						$result['html_popup'] = $htmlPopup;
					} else {
						$this->_forward('add', 'index', 'wishlist', $params);
						return;
					}
	
				} catch (Exception $e) {
					$this->messageManager->addException($e, __('You can\'t login right now.'));
					$this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
					$result['success'] = false;
				}
			} else {
				$product = $this->_initProduct();
				$this->_coreRegistry->register('product', $product);
				$this->_coreRegistry->register('current_product', $product);
	
				$htmlPopup = $this->_ajaxWishlistHelper->getErrorHtml($product);
				$result['success'] = false;
				$result['html_popup'] = $htmlPopup;
			}
			$this->getResponse()->representJson(
				$this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
			);
		}
	
	
}
