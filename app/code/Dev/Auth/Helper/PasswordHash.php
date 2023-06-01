<?php

namespace Dev\Auth\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class PasswordHash extends AbstractHelper
{
       public function CheckPassword($password, $stored_password_hash)
       {	
       	// print_r($password);
       	// print_r($stored_password_hash);
       	// var_dump(password_verify($password, $stored_password_hash));
           	return password_verify($password, $stored_password_hash);
       }
}