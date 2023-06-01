<?php

namespace Gearup\Feedback\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Gearup\Feedback\Model\FeedbackFactory;

class Postdata extends Action
{
    private $resultJsonFactory;

    private $feedbackFactory;

    public function __construct(Context $context, JsonFactory $resultJsonFactory, FeedbackFactory $feedbackFactory)
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->feedbackFactory = $feedbackFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $post = $this->getRequest()->getPost();
        $data = array();

        $data['email'] = $post['email'];
        $data['rating'] = $post['rating'];

        $model = $this->feedbackFactory->create();
        $model->addData($data);
        $model->save();

        $result->setData(['html' => $data['rating'].'.0']);
        return $result;
    }
}
