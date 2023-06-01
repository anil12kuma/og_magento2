<?php
namespace Dev\RestApi\Model\Api;
use Dev\RestApi\Api\ResponseSingleObjectInterface;
use Magento\Framework\DataObject;

/**
 * Class ResponseSingleObject
 */
class ResponseSingleObject extends DataObject implements ResponseSingleObjectInterface
{

    /**
     * 
     * @return string
     */
    public function getToken()
    {
        return $this->_getData('token');
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token)
    {
        return $this->setData('token', $token);
    }
}
