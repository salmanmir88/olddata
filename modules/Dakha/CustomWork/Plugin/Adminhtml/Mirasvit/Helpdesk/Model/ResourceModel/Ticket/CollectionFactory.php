<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Plugin\Adminhtml\Mirasvit\Helpdesk\Model\ResourceModel\Ticket;

class CollectionFactory
{
     public function afterGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        $collection,
        $requestName
    ) {
       if($requestName=='helpdesk_ticket_adminlisting_data_source'){
          $collection->addFieldToFilter('admin_user',['in'=>['customer']]);
       }
       if($requestName=='helpdesk_ticket_listing_data_source'){
          $collection->addFieldToFilter('admin_user',['neq'=>'customer']);
       } 
       return $collection;
     }
}     