<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\ResourceModel;


use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;

class Brand  extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CategorySetupFactory
     */
    protected $_categorySetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $_setup;

    /**
//     * @var \Magestore\Shopbybrand\Model\Indexer\Product\Processor
     */
//    protected $productIndexerProcessor;

    /**
//     * @var \Magestore\Shopbybrand\Model\Indexer\Url\Processor
     */
//    protected $urlIndexerProcessor;

    /**
     * @var bool
     */
    protected $allowReIndex = true;

    /**
     * Brand constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param CategorySetupFactory $categorySetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetupInterface
//     * @param \Magestore\Shopbybrand\Model\Indexer\Product\Processor $processor
//     * @param \Magestore\Shopbybrand\Model\Indexer\Url\Processor $urlProcessor
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CategorySetupFactory $categorySetupFactory,
        // ModuleDataSetupInterface $moduleDataSetupInterface,
//        \Magestore\Shopbybrand\Model\Indexer\Product\Processor $processor,
//        \Magestore\Shopbybrand\Model\Indexer\Url\Processor $urlProcessor,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_objectManager = $objectManager;
        $this->_categorySetupFactory = $categorySetupFactory;
        // $this->_setup = $moduleDataSetupInterface;
//        $this->productIndexerProcessor = $processor;
//        $this->urlIndexerProcessor = $urlProcessor;
        /* Fix compile by ronald */
        $this->_attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        /* end fix*/
    }

    /**
     * {@inheritdoc}
     */

    protected function _construct()
    {
        $this->_init('ms_brand', 'id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        if (!$object->getId()) {
            $this->allowReIndex = false;
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);
        /** @var StockItemInterface $object */
        if ($this->allowReIndex) {
            // @TODO re-enable
//            $this->productIndexerProcessor->reindexRow($object->getId());
//            $this->urlIndexerProcessor->reindexRow($object->getId());
        }

        return $this;
    }


    /**
     * @param $brand
     *
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addOption($brand)
    {
        $prefix = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getTablePrefix();
        $attributeCode = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getAttributeCode();
        $brandStoreId = 0;
        if ($brand->getOptionId()) {
            if ($brand->getStoreId()) {
                $brandStoreId = $brand->getStoreId();
            }
            $select = $this->getConnection()->select()
                ->from(['eao' => $prefix . 'eav_attribute_option'], ['option_id', 'eaov.value', 'eaov.store_id'])
                ->join(['ea' => $prefix . 'eav_attribute'], 'eao.attribute_id=ea.attribute_id', [])
                ->join(['eaov' => $prefix . 'eav_attribute_option_value'], 'eao.option_id=eaov.option_id', [])
                ->where('ea.attribute_code=?', $attributeCode)
                ->where('eao.option_id=?', $brand->getOptionId())
                ->where('eaov.store_id=?', $brandStoreId);
            $storeValue = $this->getConnection()->fetchAll($select);
            if (count($storeValue)) {
                foreach ($storeValue as $value) {
                    if (isset($value['value']) && $value['value']) {
                        if ($value['value'] == $brand->getBrandName()) {
                            return;
                        } else {
                            $data = [
                                'value' => $brand->getBrandName(),
                            ];
                            $where = [
                                'option_id=?' => $brand->getOptionId(),
                                'store_id=?'  => $brandStoreId,
                            ];
                            $update = $this->getConnection()->update($prefix . 'eav_attribute_option_value', $data,
                                $where);
                        }
                    }
                }
            } else {
                $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
                $attId = $eavAttribute->getIdByCode('catalog_product', $attributeCode);
                $data = [

                    'value' => $brand->getBrandName(),

                    'option_id' => $brand->getOptionId(),

                    'store_id' => $brandStoreId,

                ];
                $select = $this->getConnection()->select()
                    ->from(['eao' => $prefix . 'eav_attribute_option'], ['option_id'])
                    ->join(['ea' => $prefix . 'eav_attribute'], 'eao.attribute_id=ea.attribute_id', [])
                    ->where('ea.attribute_code=?', $attributeCode)
                    ->where('eao.option_id=?', $brand->getOptionId());
                $storeValue = $this->getConnection()->fetchAll($select);
                if (count($storeValue) == 0) {
                    $optionData = [
                        'option_id'    => $brand->getOptionId(),
                        'attribute_id' => $attId,
                        'sort_order'   => 0,

                    ];
                    $option = $this->getConnection()->insert($prefix . 'eav_attribute_option', $optionData);
                }
                try {
                    $update = $this->getConnection()->insert($prefix . 'eav_attribute_option_value', $data);
                } catch (\Exception $e) {
                }
            }
        } else {
            //$attributeId = $this->_objectManager->create('Magento\Eav\Model\Config')->getAttribute('catalog_product', $attributeCode)->getId();

            /* Fix compile by ronald */
            //$this->addNewAttributeOption($brand, $attributeId);
            /* end fix */

            //get option id
            $select = $this->getConnection()->select()
                ->from(['eao' => $prefix . 'eav_attribute_option'], ['option_id', 'eaov.value', 'eaov.store_id'])
                ->join(['ea' => $prefix . 'eav_attribute'], 'eao.attribute_id=ea.attribute_id', [])
                ->join(['eaov' => $prefix . 'eav_attribute_option_value'], 'eao.option_id=eaov.option_id', [])
                ->where('ea.attribute_code=?', $attributeCode)
                ->where('eaov.value=?', $brand->getBrandName())
                ->where('eaov.store_id=?', $brandStoreId);
            $option = $this->getConnection()->fetchAll($select);
            if (count($option)) {
                $optionId = $option[0]['option_id'];

                return $optionId;
            }
        }

        return null;
    }


    /*
     * add new attribute option
     * */
    private function addNewAttributeOption($brand, $attributeId)
    {
        // $options = $this->_attributeOptionManagement->getItems('catalog_product', $attribute_id);
        // add new attribute option
        /** @var \Magento\Eav\Model\Entity\Attribute\OptionLabel $optionLabel */
        $optionLabel = $this->optionLabelFactory->create();
        $optionLabel->setStoreId(isset($brand['store_id']) ? $brand['store_id'] : 0);
        $optionLabel->setLabel($brand['brand_name']);

        $option = $this->optionFactory->create();
        $option->setLabel($optionLabel);
        $option->setStoreLabels([$optionLabel]);
        $option->setSortOrder(0);
        $option->setIsDefault(false);
        $option->setIsDefault(false);

        $this->_attributeOptionManagement->add(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeId,
            $option
        );
    }
    /**
     * @param $is_update
     * @param $fileName
     *
     * @return array
     * @throws \Exception
     */
    public function import($is_update, $fileName)
    {
        $write = $this->getConnection();
        $write->beginTransaction();
        /** @var \Magento\Framework\File\Csv $csvObject */
        $csvObject = $this->_objectManager->create('Magento\Framework\File\Csv');
        $csvData = $csvObject->getData($fileName);
        $number = ['insert' => 0, 'update' => 0];
        /** checks columns */
        $csvFields = [
            0 => 'Name',
            1 => 'Sort Order',
            2 => 'URL Key',
            3 => 'Page Title',
            4 => 'Is Featured',
            5 => 'Status',
            6 => 'Short Description',
            7 => 'Description',
            8 => 'Meta Keywords',
            9 => 'Meta Description',
        ];

        $brandTable = $this->getTable('ms_brand');
        if ($csvData[0] == $csvFields) {
            $arrayUpdate = $this->csvGetArrName($csvData);


            $prefix = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getTablePrefix();
            $attributeCode = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getAttributeCode();
            $attributeId = $this->_objectManager->create('Magento\Eav\Model\Config')->getAttribute('catalog_product', $attributeCode)->getId();

            $optionId = 0;

            try {
                foreach ($csvData as $k => $v) {
                    if ($k == 0) {
                        continue;
                    }
                    //end of file has more then one empty lines
                    if (count($v) <= 1 && !strlen($v[0])) {
                        continue;
                    }
                    if (!empty($v[0])) {
                        $data = [
                            'brand_name'        => strlen( trim(preg_replace('/[^\w\s-]/', '', $v[0]))),
                            'position_brand'    => (is_numeric($v[1])) ? $v[1] : 0,
                            'sort_order'        => (is_numeric($v[1])) ? $v[1] : 0,
                            'url_key'           => $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->refineUrlKey($v[2]),
                            'title'        => strlen(trim(preg_replace('/[^\w\s-]/', '', $v[3]))),
                            'is_featured'       => (is_numeric($v[4])) ? $v[4] : 0,
                            'is_active'            => (is_numeric($v[5])) ? $v[5] : 0,
                            'short_description' => strlen(trim($v[6])),
                            'description'       => strlen(trim($v[7])),
                            'meta_keywords'     => strlen(trim($v[8])),
                            'meta_description'  => strlen(trim($v[9])),
                        ];
                        if ($data['url_key'] == '') {
                            $data['url_key'] = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->refineUrlKey($data['brand_name']);
                        }
                        if (in_array($v[0], $arrayUpdate)) {
                            if ($is_update) {
                                $number['update']++;
                                $write->update($brandTable, $data, 'brand_name = "' . $data['brand_name'] . '"');
                            }
                            continue;
                        }
                        /* Fix compile by ronald */
                        $this->addNewAttributeOption($data, $attributeId);
                        /* end fix */
                        if ($optionId == 0) {
                            $select = $this->getConnection()->select()
                                ->from(['eao' => $prefix . 'eav_attribute_option'],
                                    ['option_id', 'eaov.value', 'eaov.store_id'])
                                ->join(['ea' => $prefix . 'eav_attribute'], 'eao.attribute_id=ea.attribute_id', [])
                                ->join(['eaov' => $prefix . 'eav_attribute_option_value'],
                                    'eao.option_id=eaov.option_id', [])
                                ->where('ea.attribute_code=?', $attributeCode)
                                ->where('eaov.value=?', $data['brand_name'])
                                ->where('eaov.store_id=?', 0);
                            $newOption = $this->getConnection()->fetchAll($select);
                            if (count($newOption)) {
                                $optionId = $newOption[0]['option_id'];
                            }
                        } else {
                            $optionId++;
                        }
                        $data['option_id'] = $optionId;
                        $dataBrand[] = $data;
                        $number['insert']++;
                        if (count($dataBrand) >= 200) {
                            $write->insertMultiple($brandTable, $dataBrand);
                            $dataBrand = [];
                        }
                    }
                }
                if (!empty($dataBrand)) {
                    $write->insertMultiple($brandTable, $dataBrand);
                }
                $write->commit();
            } catch (\Exception $e) {
                $write->rollback();
                throw $e;
            }
        } else {
            throw new \Exception(__('Invalid file upload attempt'));
        }

        return $number;
    }

    /**
     * @param $csvData
     *
     * @return mixed
     */
    public function csvGetArrName($csvData)
    {
        $array = [];
        foreach ($csvData as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $array[] = $v[0];
        }
        $shopbybrands = $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection')
            ->addFieldToFilter('brand_name', ['in' => $array])
            ->getAllField('brand_name');

        return $shopbybrands;
    }

    /**
     * @param $csvData
     *
     * @return mixed
     */
    public function csvGetArrId($csvData)
    {
        $array = [];
        foreach ($csvData as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $array[] = $v[0];
        }
        $shopbybrands = $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection')
            ->addFieldToFilter('brand_name', ['in' => $array])
            ->getAllField('id');

        return $shopbybrands;
    }

    /**
     * @param $csvData
     *
     * @return mixed
     */
    public function csvGetArrUrl($csvData)
    {
        $array = [];
        foreach ($csvData as $k => $v) {
            if ($k == 2) {
                continue;
            }
            $array[] = $v[2];
        }
        $rewrite = $this->_objectManager->create('Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection')
            ->addFieldToFilter('request_path', ['nin' => $array])
            ->addFieldToFilter('store_id', 1)
            ->getData();

        return $rewrite;
    }

    /**
     * @param bool $allStore
     *
     * @return array
     */
    public function getCatalogBrand($allStore = false)
    {
        $prefix = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getTablePrefix();
        $attributeCode = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->getAttributeCode();
        $select = $this->getConnection()->select()
            ->from(['eao' => $prefix . 'eav_attribute_option'], ['option_id', 'eaov.value', 'eaov.store_id'])
            ->join(['ea' => $prefix . 'eav_attribute'], 'eao.attribute_id=ea.attribute_id', [])
            ->join(['eaov' => $prefix . 'eav_attribute_option_value'], 'eao.option_id=eaov.option_id', [])
            ->where('ea.attribute_code=?', $attributeCode);
        if ($allStore) {
            $select->where('eaov.store_id=?', 0);
        } else {
            $select->where('eaov.store_id !=?', 0);
        }
        $option = $this->getConnection()->fetchAll($select);

        return $option;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getAttributeOptions($value)
    {
        /** @var \Magestore\Shopbybrand\Helper\Brand $brandHelper */
        $brandHelper = $this->_objectManager->get('Magestore\Shopbybrand\Helper\Brand');
        $attributeCode = $brandHelper->getAttributeCode();

        $select = $this->getConnection()->select()
            ->from(['eao' => $this->getTable('eav_attribute_option')], ['option_id', 'eaov.value', 'eaov.store_id'])
            ->join(['ea' => $this->getTable('eav_attribute')], 'eao.attribute_id=ea.attribute_id', [])
            ->join(['eaov' => $this->getTable('eav_attribute_option_value')], 'eao.option_id=eaov.option_id', [])
            ->where('ea.attribute_code=?', $attributeCode);

        $select->where('eaov.value=?', $value);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * delete data from table.
     *
     * @param $table
     * @param array $where
     *
     * @throws LocalizedException
     */
    public function deleteData($table, array $where = [])
    {
        if (empty($where)) {
            return;
        }

        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $connection->delete($table, $where);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    public function deleteUrlRewrite(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->deleteData(
            $this->getTable('url_rewrite'),
            ['target_path = ?'=> 'brand/index/viewbrand/brand_id/' . $object->getId()]
        );

        return $this;
    }

}