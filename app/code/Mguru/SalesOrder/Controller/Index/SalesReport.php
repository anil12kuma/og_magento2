<?php
namespace Mguru\SalesOrder\Controller\Index;

/**
 * Sales Report
 * Mguru Digital
 */
class SalesReport extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_totalorder;
	protected $productRepository;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Mguru\SalesOrder\Block\Orders $totalorder,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository
	)
	{	
		$this->_totalorder = $totalorder;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
		$this->productRepository = $productRepository;
		
	}

	public function execute()
	{
		$post = $this->getRequest()->getPost();
		 
		$from = $post['fromDate'];
		$to = $post['toDate'];
		
		$orderDatamodel = $this->_totalorder->getOrderCollectionByDate($from, $to);

		$heading = [
					__('Order Id'),  //1	
					__('Order Date'), //2

					__('Customer Name'),	//3
					__('Cust ID'),	//4
					__('Customer number'), //5
					__('Shipping Address'), //6
					__('City'), //7
					__('State'), //8
					__('Pin Code'), //9

					__('Gift Message'),	//10
					__('Ordered Qty'), //11
					__('Order Status'), //12
					__('Payment Method'), //13
					
					__('Courier	Mode'), //14
					__('AWB Number'), //15
					__('Coupon Code'), //16
					__('Discount Applied'), //16A
					
					__('Product Ordered'), //17
					__('Sku'),//18
                    __('Size'),//28
					__('Color'),//29
					__('HSN Code'),//29
					__('Brand'),//18
					__('Item Qty'), //19
					__('MRP Value'),//20
					__('Item Discount'),//21
					__('Tax/GST'),//21
					__('Net Value'),//22
					
					__('Coupon value'), //23
					__('Online Payment'), //24
					__('Wallet'), //25
					__('Shipping Charge'), //26
					__('Total Value') //27
				   ];
		$outputfile = "OrderReport/OrderReports_".date('YmdHis').".csv";
		$handle = fopen($outputfile, 'w');
		fputcsv($handle, $heading);	

		$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
		// $orderDatamodel->getSelect()->limit(500);
		$itemsName = array();	$y = array(); $itemsSku = array();  $mrp = array(); $netValue = array(); $discountArray = array(); $itemSize = array(); $itemColor = array();
		foreach ($orderDatamodel as $key => $value) {
			$order = $object_manager->create('\Magento\Sales\Model\Order')->load($value->getId());
			// $orderItems = $order->getAllItems(); /*gives all simepla and configurable product name from ordered item. */
			$orderItems = $order->getAllVisibleItems();

			$timefc = $object_manager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
			$orderDate = $timefc->formatDateTime($order->getUpdatedAt());
			

			$fullCutomerName = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
			$payment = $order->getPayment();
			$method = $payment->getMethodInstance();
			$methodTitle = $method->getTitle();

			/* get Billing details */  
			$billingaddress = $order->getBillingAddress();
			$billingcity = $billingaddress->getCity();      
			$billingstreet = $billingaddress->getStreet();
			$billingpostcode = $billingaddress->getPostcode();
			$billingtelephone = $billingaddress->getTelephone();
			$billingstate_code = $billingaddress->getRegionCode();
			$fullBillingStreet = "";
			foreach ($billingstreet as $value) {
				$fullBillingStreet .= $value." ";
			}
			
			/* get tracking details. */
			$trackNumbers = 0;
			$trackTitles = "";
			$tracksCollection = $order->getTracksCollection();
			foreach ($tracksCollection->getItems() as $track) {
				$trackNumbers = $track->getTrackNumber();
				$trackTitles = $track->getTitle();
			}

			// applied discount code
			$appliedDiscount = '';
			if($order->getAppliedRuleIds() == 2){
				//$appliedDiscount = $order->getAppliedRuleName();
				$appliedDiscount = 'CRTDSC'.' '.'10%';
			}elseif($order->getAppliedRuleIds() == 5){
				$appliedDiscount = '15%';
			}

			$row = [
				$order->getIncrementId(), //1
				$orderDate, //2

				$fullCutomerName, //3
				$order->getCustomerEmail(), // 4
				$billingtelephone, //5
				$fullBillingStreet, //6
				$billingcity, //7
				$billingstate_code, //8
				$billingpostcode, //9

				$order->getGiftMessage(), //10
				$order->getTotalItemCount(), //11
				$order->getStatus(), //12
				$methodTitle, //13

				$trackTitles, //14
				$trackNumbers, //15
				$order->getCouponCode(), //16
				$appliedDiscount //16A
			];

			$row1 = [
				$order->getBaseDiscountAmount(), //23
				$order->getGrandTotal(), //24
				$order->getData('amstorecredit_amount'), //25
				$order->getShippingAmount(), //26
				$order->getGrandTotal() + $order->getData('amstorecredit_amount') +$order->getShippingAmount() //27
			];
			
			foreach ($orderItems as $item) {
				/*if($item->getProductType() == 'configurable'){*/
					//print_r($item);
					$y[] = $item->getQtyOrdered(); //19
					$itemsName[] = $item->getName();	//17
					$pid = $item->getId();
					$itemsSku[] = $item->getSku(); //18
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        			$productRepository = $objectManager->create('\Magento\Catalog\Model\ProductRepository');
					$product = $productRepository->get($item->getSku());
					$itemColor[] = $product->getAttributeText('color'); //28
					$itemSize[] = $product->getAttributeText('size'); //29
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $products = $objectManager->create('Magento\Catalog\Model\Product')->load($pid);
					$_condition = $product->getAttributeText('manufacturer');
                    $prod= $product->setStoreId(0);
                    $itemsBrand[] =$product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
                    $itemHsn[] = $product->getResource()->getAttribute('hsn')->getFrontend()->getValue($product);
					//$itemHsn[] = $product->getAttributeText('hsn_code'); //29
                    //$itemsBrand[]  = $product()->getAttributeText('manufacturer');
                    //$itemsBrand[] = $products->getData('manufacturer');
			        $mrp[] = $item->getOriginalPrice(); //20
					$netValue[] = $item->getPrice(); //22
					$discount = $item->getOriginalPrice() - $item->getPrice();
					$tax =$item->getBasePriceInclTax() - $item->getPrice();
					$discountArray[] = $discount - $tax; //21
					$taxArray[] =$item->getBasePriceInclTax() - $item->getPrice();
				/*}else{
					continue;
				}*/
				
				$rowfinal = array_merge($row,$itemsName,$itemsSku,$itemSize,$itemColor,$itemHsn,$itemsBrand,$y,$mrp,$discountArray,$taxArray,$netValue,$row1);
				fputcsv($handle, $rowfinal);
				unset($y); unset($itemsName); unset($itemsSku); unset($itemSize); unset($itemColor);  unset($itemHsn); unset($itemsBrand); unset($mrp); unset($netValue); unset($discountArray); unset($taxArray);
			}

			unset($itemsName);
			unset($noOfPacketCount);
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
	         readfile($file);
	     }
	}	

}
