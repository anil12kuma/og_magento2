<?php
namespace Mguru\SalesOrder\Controller\Index;

class CreditHistoryReport extends \Magento\Framework\App\Action\Action
{
	public function execute() {
		$post = $this->getRequest()->getPost();		 
		
		$heading = [
					__('Trnx Time'),	
					__('Trnx Amount'),
					__('Trnx Status'),
					__('Order Ref.'),
					__('Remarks'),
					__('Email ID'),
				   ];

		$outputfile = "CreditReport/CreditHistoryReports_".date('Ymd_His').".csv";
		$handle = fopen($outputfile, 'w');
		fputcsv($handle, $heading);	

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();

		$sql = "SELECT ce.email, asch.is_deduct, asch.difference, asch.message, asch.store_credit_balance, asch.action_data, asch.created_at FROM amasty_store_credit_history asch JOIN customer_entity ce ON ce.entity_id = asch.customer_id";
		$results = $connection->fetchAll($sql);

		foreach ($results as $result) {
			if($result['is_deduct'] == 0){
				$trnxStatus = 'Credit';
			}else{
				$trnxStatus = 'Debit';
			}
			$orderRef = preg_replace('/[^A-Za-z0-9\-]/', '', $result['action_data']);

			$row = [
				$result['created_at'],
				$result['difference'],
				$trnxStatus,
				$orderRef,
				$result['message'],
				$result['email'],
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
