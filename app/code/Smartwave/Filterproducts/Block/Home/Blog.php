<?php

namespace Smartwave\Filterproducts\Block\Home;

class Blog extends \Magento\Framework\View\Element\Template
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context)
	{
		parent::__construct($context);
	}

	public function sayHello()
	{
		// return __('Hello World');
		$curl = curl_init();
        $url = "http://production.wildcountry.in/wp-json/wp/v2/posts?content_type=3&per_page=4";

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET'
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
        // print_r($response);
	}

}	