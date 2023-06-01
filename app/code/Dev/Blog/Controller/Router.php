<?php
namespace Dev\Blog\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    protected $actionFactory;
    protected $_response;
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response
    )
    {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $identifier = urldecode($identifier);
        $pathInfo = explode('/', $identifier);
        if (isset($pathInfo[0]) && $pathInfo[0] != 'blog') {
            return false;
        }

        if (isset($pathInfo[1]) && $pathInfo[1] == 'page') {
            $request->setModuleName('blog')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('param1', $pathInfo[1]) //custom parameters
                ->setParam('param2', $pathInfo[2]); //custom parameters
        } else {
            $request->setModuleName('blog')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('param1', $pathInfo[1]);
        }

        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }
}