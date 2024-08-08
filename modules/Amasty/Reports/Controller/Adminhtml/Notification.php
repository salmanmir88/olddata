<?php

declare(strict_types=1);

namespace Amasty\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Notification extends Action
{
    const ADMIN_RESOURCE = 'Amasty_Reports::notification';
}
