<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class SampleFile extends \Magestore\Shopbybrand\Controller\Adminhtml\Brand
{
    /**
     * Execute action
     */
    public function execute()
    {
        $fileName = 'shopbybrand.csv';

        /** @var \Magento\Framework\App\Response\Http\FileFactory $fileFactory */
        $fileFactory = $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory');

        return $fileFactory->create(
            $fileName,
            $this->getShopbyBrandSampleData(),
            DirectoryList::VAR_DIR
        );
    }

    public function getShopbyBrandSampleData()
    {
        /** @var \Magento\Framework\Module\Dir $moduleReader */
        $moduleReader = $this->_objectManager->get('Magento\Framework\Module\Dir');
        /** @var \Magento\Framework\Filesystem\DriverPool $drivePool */
        $drivePool = $this->_objectManager->get('Magento\Framework\Filesystem\DriverPool');
        $drive = $drivePool->getDriver(\Magento\Framework\Filesystem\DriverPool::FILE);

        return $drive->fileGetContents($moduleReader->getDir('Magestore_Shopbybrand')
            . DIRECTORY_SEPARATOR . '_fixtures' . DIRECTORY_SEPARATOR . 'shopbybrand.csv');
    }
}
