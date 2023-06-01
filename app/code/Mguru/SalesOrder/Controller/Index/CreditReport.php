<?php
namespace Mguru\SalesOrder\Controller\Index;

class CreditReport extends \Magento\Framework\App\Action\Action
{
	public function execute() {
		$post = $this->getRequest()->getPost();		 
		
		$heading = [
					__('Customer Email'),	
					__('Credit Balance'),
				   ];

		$outputfile = "CreditReport/CreditReports_".date('Ymd_His').".csv";
		$handle = fopen($outputfile, 'w');
		fputcsv($handle, $heading);	

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();

		$sql = "SELECT ce.email, ascr.store_credit FROM amasty_store_credit ascr JOIN customer_entity ce on ce.entity_id = ascr.customer_id";
		$results = $connection->fetchAll($sql);

		foreach ($results as $result) {
			$row = [
				$result['email'],
				$result['store_credit'],
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
