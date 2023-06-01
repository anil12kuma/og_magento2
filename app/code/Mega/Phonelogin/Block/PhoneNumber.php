<?php
/**
 *
 * @category   MaxMage
 * @package    mc-magento2
 * @author     MaxMage Core Team <maxmagedev@gmail.com>
 * @date       1/14/2018
 * @copyright  Copyright © 2018 MaxMage. All rights reserved.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @file       PhoneNumber.php
 */

namespace Mega\Phonelogin\Block;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mega\Phonelogin\Helper\CheckoutHelper;

class PhoneNumber extends Template
{

    /**
     * @var Json
     */
    protected $jsonHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInformation;

    /**
     * PhoneNumber constructor.
     * @param Context $context
     * @param Json $jsonHelper
     */
    public function __construct(
        Context $context,
        Json $jsonHelper,
        CountryInformationAcquirerInterface $countryInformation,
        CheckoutHelper $helper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->countryInformation = $countryInformation;
        parent::__construct($context);
    }

    /**
     * @return bool|string
     */
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
