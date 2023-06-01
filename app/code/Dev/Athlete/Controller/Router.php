<?php
namespace Dev\Athlete\Controller;

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
        if (isset($pathInfo[0]) && $pathInfo[0] != 'athlete') {
            return false;
        }

        if (!isset($pathInfo[1])) {
            return false;
        }

        $username = $pathInfo[1];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "http://production.wildcountry.in/wp-json/wcy/v3/athlete_detail/$username");
        $athelete_details = curl_exec($ch);
        curl_close($ch);
        $athelete_details = json_decode($athelete_details);
        if (isset($athelete_details->data) && $athelete_details->data->status == 404) {
            return false;
        }

        $request->setModuleName('athlete')
            ->setControllerName('details')
            ->setActionName('index')
            ->setParam('username', $pathInfo[1]); //custom parameters

        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }
}