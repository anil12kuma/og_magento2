<?php
namespace Dev\RestApi\Model\Api;
use Dev\RestApi\Api\ResponseMsgInterface;
use Magento\Framework\DataObject;

/**
 * Class ResponseMsg
 */
class ResponseMsg extends DataObject implements ResponseMsgInterface
{

    /**
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->_getData('message');
    }

    /**
     * @param string $msg
     * @return $this
     */
    public function setMessage(string $msg)
    {
        return $this->setData('message', $msg);
    }
}
