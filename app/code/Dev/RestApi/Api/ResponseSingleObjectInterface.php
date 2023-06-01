<?php
namespace Dev\RestApi\Api;

interface ResponseSingleObjectInterface
{
    /**
     * @return string
     */
    public function getToken();

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token);
}
