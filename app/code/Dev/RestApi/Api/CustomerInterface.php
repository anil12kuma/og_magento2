<?php
namespace Dev\RestApi\Api;

interface CustomerInterface
{

    /**
    * Change password manually
    * 
    * @param string $customerId
    * @param string $password
    * @return object[]
    */
    public function changePassword(string $customerId, string $password);

    /**
    * creaet customer
    * 
    * @param string $email
    * @param string $firstname
    * @param string $lastname
    * @param string $password
    * @return object[]
    */
    public function createCustomer($email, $firstname, $lastname, $password);
}
