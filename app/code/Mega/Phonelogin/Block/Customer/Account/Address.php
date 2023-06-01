<?php

namespace Mega\Phonelogin\Block\Customer\Account;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Mega\Phonelogin\Helper\CheckoutHelper;

class Address extends \Magento\Framework\View\Element\Template
{


    /**
     * @var Json
     */
    private $jsonHelper;
    /**
     * @var CheckoutHelper
     */
    private $helper;
    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformation;

    public function __construct(
        Template\Context $context,
        Json $jsonHelper,
        CountryInformationAcquirerInterface $countryInformation,
        CheckoutHelper $helper,
        array $data = []
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->countryInformation = $countryInformation;
        parent::__construct($context, $data);
    }


    public function phoneConfig()
    {
        $config  = [
            // "utilsScript"  => $this->getViewFileUrl('Mega_Phonelogin::js/utils.js'),
             "nationalMode" => false,
            // "separateDialCode" => false,
            "formatOnDisplay" => false
            //  "preferredCountries" => [$this->helper->preferedCountry()]
        ];

        if ($this->helper->allowedCountries()) {
            $config["onlyCountries"] = explode(",", $this->helper->allowedCountries());
        }

        return $this->jsonHelper->serialize($config);
    }
}
