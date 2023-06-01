##Shop By Brand Extension
Shop By Brand extension allows creating attributes, brand pages, brand logo sliders effectively using blocks, templates, layouts and widgets thereby gain the user trust and credibility.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1.	Get the extension’s version number
Note: Refer to the below link to get the version number of the downloaded extension.
https://devdocs.magento.com/extensions/install/#get-the-extensions-composer-name-and-version
2.	Navigate to your Magento project directory and update your composer.json file
Command: composer require sparsh/magento-2-brand-extension:<version>


#Enable Extension:
- php bin/magento module:enable Sparsh_Brand
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_Brand
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush
