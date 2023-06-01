<?php
namespace Dev\RestApi\Api;

interface SocialUserInterface
{

    /**
     * Return a social user's token.
     *
     * @param string $sub
     * @param string $email
     * @return object[]
     */
    public function getToken($sub, $email);

}
