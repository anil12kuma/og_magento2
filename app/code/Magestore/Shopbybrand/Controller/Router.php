<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var bool
     */
    protected $dispatched;

    /**
     * @var \Magestore\Shopbybrand\Model\Brand
     */
    protected $_brandCollection;

    /**
     * @var \Magestore\Shopbybrand\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @var \Magestore\Shopbybrand\Helper\Data
     */
    protected $_brandHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;


    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     * @param ManagerInterface $eventManager
     * @param \Magestore\Shopbybrand\Model\Brand $brandCollection
     * @param \Magestore\Shopbybrand\Helper\Data $brandHelper
     * @param \Magestore\Shopbybrand\Model\SystemConfig $systemConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        ManagerInterface $eventManager,
        \Magestore\Shopbybrand\Model\Brand $brandCollection,
        \Magestore\Shopbybrand\Helper\Data $brandHelper,
        \Magestore\Shopbybrand\Model\SystemConfig $systemConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->response = $response;
        $this->_brandHelper = $brandHelper;
        $this->_brandCollection = $brandCollection;
        $this->_systemConfig = $systemConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ActionInterface
     */
    public function match(RequestInterface $request)
    {
        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = strlen($urlKey);
            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'magestore_shopbybrand_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $urlKey = $condition->getUrlKey();
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);

                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                );
            }
            if (!$condition->getContinue()) {
                return null;
            }
            $route = $this->_systemConfig->getFrontendUrlPath();
            if ($urlKey == $route) {
                $request->setModuleName('brand')
                    ->setControllerName('index')
                    ->setActionName('index');
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                $this->dispatched = true;

                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                );
            }
            $url_prefix = $url_suffix = '';
            $str2 = trim($url_prefix);
            $identifiers = explode('/', $urlKey);
            // Check Brand Url Key
            if ((count($identifiers) == 2 && $identifiers[0] == $url_prefix && strpos($identifiers[1],
                        $url_suffix)) || (strlen($str2) == '' && count($identifiers) == 1)
            ) {
                if (count($identifiers) == 2) {
                    $brandUrl = str_replace($url_suffix, '', $identifiers[1]);
                }
                if (strlen($str2) == '' && count($identifiers) == 1) {
                    $brandUrl = str_replace($url_suffix, '', $identifiers[0]);
                }

                $brand = $this->_brandCollection->getCollection()
                    ->addFieldToFilter('is_active', ['eq' => 1])
                    ->addFieldToFilter('url_key', ['eq' => $brandUrl])
                    ->getFirstItem();
                if ($brand && $brand->getId()) {
                    $request->setModuleName('brand')
                        ->setControllerName('index')
                        ->setActionName('viewbrand');
                        //->setParam('brand_id', $brand->getId());
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;

                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                }
            }
        }
    }
}