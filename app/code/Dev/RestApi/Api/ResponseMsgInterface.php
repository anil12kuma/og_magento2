<?php
namespace Dev\RestApi\Api;

interface ResponseMsgInterface
{
    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $msg
     * @return $this
     */
    public function setMessage(string $msg);
}
