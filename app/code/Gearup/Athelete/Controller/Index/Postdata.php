<?php

namespace Gearup\Athelete\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Gearup\Athelete\Model\AtheleteFactory;

class Postdata extends Action
{
    private $resultJsonFactory;

    private $atheleteFactory;

    public function __construct(Context $context, JsonFactory $resultJsonFactory, AtheleteFactory $atheleteFactory)
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->atheleteFactory = $atheleteFactory;
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
        $data['activities'] = $post['activities'] ? implode(',',$post['activities']) : '';
        $data['message'] = $post['message'] ? $post['message'] : '';

        $model = $this->atheleteFactory->create();
        $model->addData($data);
        $model->save();

        $result->setData(['html' => true]);
        return $result;
    }
}
