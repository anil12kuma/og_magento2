<?php
declare(strict_types=1);

namespace Amasty\GoogleAddressAutocomplete\Model;

use Amasty\GoogleAddressAutocomplete\Model\ResourceModel\Region\CollectionFactory;

class GetRegionsList
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Returns a properly formatted list of regions that follows the following format:
     * [ 'countryId' => ['regionCode' => 'regionId'] ]
     *
     * Used for address form on the checkout.
     *
     * @return array<string, array<string, string>>
     */
    public function execute(): array
    {
        $collection = $this->collectionFactory->create();
        return $collection->fetchRegions();
    }
}
