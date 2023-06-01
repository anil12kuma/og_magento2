<?php

namespace Gearup\Partner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Gearup\Partner\Model\PartnerFactory;

class Postdata extends Action
{
    private $resultJsonFactory;

    private $partnerFactory;

    public function __construct(Context $context, JsonFactory $resultJsonFactory, PartnerFactory $partnerFactory)
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->partnerFactory = $partnerFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $post = $this->getRequest()->getPost();
        $data = array();

        $data['name'] = $post['name'];
        $data['email'] = $post['email'];
        $data['phone'] = $post['phone'];
        $data['selection_area'] = $post['selection_area'] ? $post['selection_area'] : '';
        $data['selections'] = $post['selections'] ? implode(',',$post['selections']) : '';
        $data['message'] = $post['message'] ? $post['message'] : '';

        $model = $this->partnerFactory->create();
        $model->addData($data);
        $model->save();

        $result->setData(['html' => true]);
        return $result;
    }
}
