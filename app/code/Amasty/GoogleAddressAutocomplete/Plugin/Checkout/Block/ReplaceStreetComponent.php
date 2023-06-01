<?php
declare(strict_types=1);

namespace Amasty\GoogleAddressAutocomplete\Plugin\Checkout\Block;

use Amasty\GoogleAddressAutocomplete\Model\ConfigProvider;
use Magento\Checkout\Block\Checkout\AttributeMerger;

class ReplaceStreetComponent
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param AttributeMerger $subject
     * @param array $config
     * @see AttributeMerger::merge
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMerge(AttributeMerger $subject, array $config): array
    {
        if (isset($config['street'])
            && $this->configProvider->isAddressSuggestionEnabled()
            && $this->configProvider->getGoogleMapsKey()) {
            $config['street']['children'][0]['component']
                = 'Amasty_GoogleAddressAutocomplete/js/form/element/autocomplete';
        }

        return $config;
    }
}
