<?php

namespace Gearup\Partner\Controller\Email;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;

class Send extends Action
{
    protected $resultJsonFactory;
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
    }

    public function execute()
    {
        $email = $this->getRequest()->getParam('email');
        $this->sendEmail($email);
    }

    public function sendEmail($email)
    {
        //$result = $this->resultJsonFactory->create();
        //$result->setData(['html' => "Unable to send email"]);
        $result ="Test !";
        return $result;
        /*try {
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml('Gearup'),
                'email' => $this->escaper->escapeHtml('vishal.sonkhiya@gmail.com'),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('download_app_template')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'templateVar'  => 'Download App',
                ])
                ->setFrom($sender)
                ->addTo($email)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $result->setData(['html' => 'Download link has been shared on your email id']);
            return $result;
        } catch (\Exception $e) {
            $result->setData(['html' => "Unable to send email"]);
            return $result;
        }*/
    }
}
