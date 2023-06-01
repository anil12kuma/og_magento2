<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Controller\Adminhtml\Cancel;

use Amasty\CancelOrder\Model\CancelOrderCron;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    /**
     * @var CancelOrderCron
     */
    private $cancelOrderCron;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CancelOrderCron $cancelOrderCron,
        Action\Context $context,
        \Amasty\Base\Model\Serializer $serializer,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->cancelOrderCron = $cancelOrderCron;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $data = $this->cancelOrderCron->execute();
            $message = $this->getMessage($data);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $message = __('Sorry, something seems to have gone wrong. Please, check the logs.');
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($message);
    }

    /**
     * @param \Magento\Framework\DataObject $data
     * @return \Magento\Framework\Phrase
     */
    private function getMessage(\Magento\Framework\DataObject  $data): \Magento\Framework\Phrase
    {
        $canceled = $data->getData('canceledCount');
        $total = $data->getData('totalCount');

        if ($canceled) {
            $message = __(
                '%1 orders with corresponding Status(es) were found. 
                <br> %2 orders with matching payment method conditions were successfully canceled.',
                $canceled,
                $total
            );
        } elseif ($total) {
            $message = __(
                '%1 orders with corresponding Status(es) were found, 
                but none of them matches Payment Method conditions. None of orders was canceled.',
                $total
            );
        } else {
            $message = __('There are no orders matching the conditions.');
        }

        return $message;
    }
}
