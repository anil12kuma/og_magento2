<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * Action Index
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Execute action
     */
    public function execute()
    {
        /** @var \Magestore\Shopbybrand\Model\SystemConfig $config */
        $config = $this->_objectManager->get('Magestore\Shopbybrand\Model\SystemConfig');
        if(!$config->isEnable()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            
            return $resultRedirect->setPath('csm/noroute');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Shop By Brand'));


        return $resultPage;
    }

    /**
     * Action constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
    }
}
