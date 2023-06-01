<?php

namespace Dev\Auth\Helper;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Store\Model\StoreManagerInterface;

use \Magento\Framework\App\Helper\AbstractHelper;

class CartData extends AbstractHelper
{
	protected $stockRegistry;
	protected $_quote;
	public function __construct(
    	\Magento\Quote\Model\Quote\Item $quote,
    	StockRegistryInterface $stockRegistryInterface,
    	StockRegistry $stockRegistry,
    	StoreManagerInterface $storeManager
	) {
	    $this->_quote = $quote;  
	    $this->stockRegistryInterface = $stockRegistryInterface;
	    $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
	  }

    public function checkPrductIsInStock($itemId)
    {
       $productData = $this->_quote->load($itemId);
       $stockItem = $this->stockRegistryInterface->getStockItem($productData->getProductId());
       $isInStock = $stockItem ? $stockItem->getIsInStock() : false;

       $stockStatus = $this->stockRegistry->getStockStatusBySku(
                $productData->getSku(),
                $this->storeManager->getWebsite()->getId()
            );
	   return $stockStatus->getStockItem();
    }
}
