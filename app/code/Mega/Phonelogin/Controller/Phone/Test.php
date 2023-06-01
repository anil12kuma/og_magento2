<?php
namespace Mega\Phonelogin\Controller\Phone;

class Test extends  \Magento\Framework\App\Action\Action
{

    function  execute()
    {
        echo $this->_request->getFullActionName();
        die('wor');
    }
}