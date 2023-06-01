<?php
namespace Gearup\Partner\Controller\Email;
 
use Zend\Log\Filter\Timestamp;
use Magento\Store\Model\StoreManagerInterface;
 
class Send extends \Magento\Framework\App\Action\Action
{
    const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'trans_email/ident_support/email';
     
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_logLoggerInterface;
    protected $storeManager;
     
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager,
        array $data = []
         
        )
    {
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->messageManager = $context->getMessageManager();
        $this->storeManager = $storeManager;
         
        parent::__construct($context);
         
         
    }
     
    public function execute()
    {
        $post = $this->getRequest()->getPost();
        //print_r($post);
        
        // Get form data in $post veriable

        try
        {
            // Send Mail 
            $this->_inlineTranslation->suspend();                  

            // Email sender Name and Email address             
            $sender = [
                'email' => $post['email']
            ];
             
            $sentToEmail = $this->_scopeConfig ->getValue('trans_email/ident_general/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
             
             
             
            // customemail_email_template  is a template id define in email_templates.xml file
             
            $transport = $this->_transportBuilder
            ->setTemplateIdentifier('download_app_template')
            ->setTemplateOptions(
                [
                    'area' => 'frontend',
                    'store' => $this->storeManager->getStore()->getId()
                ]
                )
                ->setTemplateVars([
                    'email'  => $post['email']
                ])
                ->setFromByScope($sender)
                ->addTo($sentToEmail,$sentToName)
                //->addTo('info@example.com','info')
                ->getTransport();
                 
                $transport->sendMessage();
                 
                $this->_inlineTranslation->resume();
                $this->messageManager->addSuccess('Email sent successfully');
                $this->_redirect('your url');
                 
        } catch(\Exception $e){
            $this->messageManager->addError($e->getMessage());
            $this->_logLoggerInterface->debug($e->getMessage());
            exit;
        }         
         
    }
}