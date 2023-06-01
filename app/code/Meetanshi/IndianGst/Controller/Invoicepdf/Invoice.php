<?php
declare(strict_types=1);

namespace Meetanshi\IndianGst\Controller\Invoicepdf;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Framework\Message\ManagerInterface;


class Invoice implements HttpGetActionInterface
{
    const SELECTED_PARAM = 'id';

    protected FileFactory $fileFactory;
    protected DateTime $dateTime;
    protected PdfInvoice $pdfInvoice;
    protected RequestInterface $request;
    protected RedirectInterface $redirect;
    protected ManagerInterface $messageManager;
    protected ResultFactory $resultFactory;
    protected CollectionFactory $collectionFactory;

    /**
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param PdfInvoice $pdfInvoice
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        DateTime          $dateTime,
        FileFactory       $fileFactory,
        PdfInvoice        $pdfInvoice,
        CollectionFactory $collectionFactory,
        RequestInterface  $request,
        RedirectInterface $redirect,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory
    )
    {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfInvoice = $pdfInvoice;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->collectionFactory->create();

            $invoiceId = $this->request->getParam(self::SELECTED_PARAM);
            $filterIds = $invoiceId ? [$invoiceId] : [];
            $collection->addFieldToFilter(
                $collection->getResource()->getIdFieldName(),
                ['in' => $filterIds]
            );

            $pdf = $this->pdfInvoice->getPdf($collection);
            $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

            return $this->fileFactory->create(
                sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
                $fileContent,
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());            
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}