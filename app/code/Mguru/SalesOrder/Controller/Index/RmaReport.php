<?php
namespace Mguru\SalesOrder\Controller\Index;

class RmaReport extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $orderItemRepository;
	protected $orderRepository;
	protected $_resource;
	//protected $_customerRepositoryInterface;
	protected $_customer;
	protected $productRepository;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\Customer $customers,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository
		//\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,

	)
	{	
		$this->_pageFactory = $pageFactory;
		$this->orderItemRepository = $orderItemRepository;
		$this->orderRepository = $orderRepository;
		$this->_resource = $resource;
		//$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->_customer = $customers;
		$this->productRepository = $productRepository;
		return parent::__construct($context);
		
	}

	public function execute()
	{

		$post = $this->getRequest()->getPost();
		 
		$fromDate = $post['fromDate']." 00:00:00";
		$toDate = $post['toDate']." 23:59:59";

		$heading = [
					__('Rma Id'),
					__('Created_At'),
					__('Order Id'),
					__('Payment Method'),
					
					__('SKU'),
					__('Size'),
					__('Color'),
					__('Product Name'),
					__('Price'),
					
					__('Qty'),
					__('Requested Return Qty'),
					__('Return/Exchange'),
					__('Return/Exchange Resolution'),
					__('Status'),
					__('Remark'),
					__('Bank Name'),
					__('Account H. Name'),
					__('Account Number'),
					__('IFSC Code'),


					__('Customer Name'),
					__('Ph No'),
					__('Email ID'),
				   ];
				   
		$outputfile = "OrderReport/RmaReports_".date('YmdHis').".csv";
		$handle = fopen($outputfile, 'w');
		fputcsv($handle, $heading);	

		// $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		// $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		// $connection = $resource->getConnection();
		$connection = $this->_resource->getConnection();

		$sql = "SELECT arr.request_id, arr.order_id, arr.created_at, arr.modified_at, arr.customer_id, arr.customer_name, arr.custom_fields,
						arri.order_item_id, arri.qty, arri.request_qty, arri.reason_id, arri.resolution_id ,
						arrea.title as reason, 
						arres.title as resolution,
						ars.title as rma_status
				FROM amasty_rma_request arr
				JOIN amasty_rma_request_item arri ON arri.request_id = arr.request_id
				JOIN amasty_rma_reason arrea ON arrea.reason_id = arri.reason_id
				JOIN amasty_rma_resolution arres on arres.resolution_id = arri.resolution_id
				JOIN amasty_rma_status ars on ars.status_id = arr.status
				WHERE arr.created_at BETWEEN '".$fromDate."' AND '".$toDate."'";
		$results = $connection->fetchAll($sql);

		
		foreach ($results as $result) {			
			$customer_id = $result['customer_id'];
			//$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customer_id);
			//$customer = $this->_customerRepositoryInterface->getById($customer_id);
			$customer = $this->_customer->load($customer_id);
			$emailid = $customer->getEmail();
			$customerMob = ($customer->getMobile())?$customer->getMobile():'';
			$customername = $result['customer_name'];

			$status = $result['rma_status'];

			$itemCollection = $this->orderItemRepository->get($result['order_item_id']);
			$options = $itemCollection->getProductOptions();

			$itemSku = $itemCollection->getSku();
			$product = $this->productRepository->get($itemSku);
			$size  = $product->getAttributeText('size');
			$color  = $product->getAttributeText('color');
			$itemName = $itemCollection->getName();
			
			
			$itemPrice = $itemCollection->getPrice();

			$orderId = $result['order_id'];
			$order = $this->orderRepository->get($orderId);
			$orderIncrementId = $order->getIncrementId(); // To get order incremental id
			$payment = $order->getPayment();
			$method = $payment->getMethodInstance();
			$methodTitle = $method->getTitle();

			// Convert custom fields in individual fields
			$customFields = json_decode($result['custom_fields'], true);			
			$remarks = (!empty($customFields['Comment'])) ? $customFields['Comment'] : 'NA';
			$bankName = (!empty($customFields['bank_name'])) ? $customFields['bank_name'] : 'NA';
			$accountHName = (!empty($customFields['account_holder_name'])) ? $customFields['account_holder_name'] : 'NA';
			$accountNum = (!empty($customFields['account_number'])) ? $customFields['account_number'] : 'NA';
			$ifscCode = (!empty($customFields['ifsc_code'])) ? $customFields['ifsc_code'] : 'NA';

			$row = [
				$result['request_id'],
				$result['created_at'],

				$orderIncrementId,
				$methodTitle,
				
				$itemSku,
				$size,
				$color,
				$itemName,
				$itemPrice,
				$result['qty'],
				$result['request_qty'],

				$result['resolution'],
				$result['reason'],
				$status,
				$remarks,
				$bankName,
				$accountHName,
				$accountNum,
				$ifscCode,

				$customername,
				$customerMob,
				$emailid,
			];

			fputcsv($handle, $row);
		}	
		
		$this->downloadCsv($outputfile);	   

		 
	}

	public function downloadCsv($file)
	{
	     if (file_exists($file)) {
	         //set appropriate headers
	         header('Content-Description: File Transfer');
	         header('Content-Type: application/csv');
	         header('Content-Disposition: attachment; filename='.basename($file));
	         header('Expires: 0');
	         header('Cache-Control: must-revalidate');
	         header('Pragma: public');
	         header('Content-Length: ' . filesize($file));
	         /*ob_clean();flush();*/
	         readfile($file);
	     }
	}

}
