<?php
namespace Dev\RestApi\Model\Api;
use Dev\RestApi\Api\SocialUserInterface;

class ProductItemList implements SocialUserInterface
{
	/**
     * Get Token
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->_get('token');
    }

    /**
     * Set token
     *
     * @param string $id
     * @return $this
     */
    public function setToken($token){
        return $this->setData('token', $token);
    }

}