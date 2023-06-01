<?php
namespace Mguru\SalesOrder\Block;

/**
 * 
 */
class Orders extends \Magento\Framework\View\Element\Template
{
	
    protected $_orderCollectionFactory;

    public function __construct(
        
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory 
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        

    }
    
    public function getOrderCollectionByDate($from, $to)
	   {
	   		
	    //$fromDate = "2020-09-01"; // Y-m-d
		//$toDate = "2020-09-22";
		$fromDate = $from." 00:00:00";
		$toDate = $to." 23:59:59";
		
	       $collection = $this->_orderCollectionFactory->create()
	         // ->addFieldToSelect('*')
	         ->addFieldToFilter('created_at', array('from' => $fromDate, 'to' => $toDate))
	         ->setOrder(
	                'created_at',
	                'desc'
	            );
	 
	     return $collection;

	    }
}