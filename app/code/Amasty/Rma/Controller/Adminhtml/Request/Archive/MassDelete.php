<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */

declare(strict_types=1);

namespace Amasty\Rma\Controller\Adminhtml\Request\Archive;

use Amasty\Rma\Controller\Adminhtml\Request\AbstractMassDelete;

class MassDelete extends AbstractMassDelete
{
    const ADMIN_RESOURCE = 'Amasty_Rma::archive_delete';
}
