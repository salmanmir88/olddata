<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.2.14
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Helpdesk\Controller\Adminhtml\Department;

class MassChange extends \Mirasvit\Helpdesk\Controller\Adminhtml\MassChange
{
    /**
     * MassChange constructor.
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Department $departmentResource
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory $collectionFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\ResourceModel\Department $departmentResource,
        \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory $collectionFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Backend\App\Action\Context $context
    ) {
        $permission               = 'Mirasvit_Helpdesk::helpdesk_department';
        parent::__construct($filter, $context, $permission, $departmentResource, $collectionFactory);
    }
}
