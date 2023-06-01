<?php

declare(strict_types=1);

namespace Amasty\GroupedOptions\Model\SeoOptionsModifier;

use Amasty\GroupedOptions\Api\Data\GroupAttrInterface;
use Amasty\GroupedOptions\Model\FakeKeyGenerator;
use Amasty\GroupedOptions\Model\GroupAttr\DataProvider;
use Amasty\ShopbySeo\Model\SeoOptionsModifier\UniqueBuilder;

class GroupAliases
{
    /**
     * @var UniqueBuilder|null
     */
    private $uniqueBuilder;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var FakeKeyGenerator
     */
    private $fakeKeyGenerator;

    public function __construct(
        DataProvider $dataProvider,
        FakeKeyGenerator $fakeKeyGenerator,
        array $data = []
    ) {
        $this->uniqueBuilder = $data['uniqueBuilder'] ?? null;
        $this->dataProvider = $dataProvider;
        $this->fakeKeyGenerator = $fakeKeyGenerator;
    }

    public function modify(array &$optionsSeoData, int $storeId, array $attributeIds = []): void
    {
        foreach ($attributeIds as $id => $code) {
            $data = $this->getAliasGroup((int) $id);
            if ($data) {
                foreach ($data as $key => $record) {
                    if ($this->getUniqueBuilder()) {
                        $alias = $this->getUniqueBuilder()->execute($record[GroupAttrInterface::URL], (string) $key);
                    } else {
                        $alias = $record[GroupAttrInterface::URL];
                    }
                    $optionsSeoData[$storeId][$code][$record[GroupAttrInterface::GROUP_CODE]] = $alias;
                }
            }
        }
    }

    private function getAliasGroup(int $attributeId): array
    {
        $data = [];
        $groups = $this->dataProvider->getGroupsByAttributeId($attributeId);

        foreach ($groups as $group) {
            $id = $this->fakeKeyGenerator->generate((int) $group->getGroupId());
            $data[$id][GroupAttrInterface::GROUP_CODE] = $group->getGroupCode();
            $data[$id][GroupAttrInterface::URL] = $group->getUrl() ?: $group->getGroupCode();
        }

        return $data;
    }

    private function getUniqueBuilder(): ?UniqueBuilder
    {
        return $this->uniqueBuilder;
    }
}
