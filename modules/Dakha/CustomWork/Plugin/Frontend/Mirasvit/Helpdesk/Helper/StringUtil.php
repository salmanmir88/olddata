<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Plugin\Frontend\Mirasvit\Helpdesk\Helper;

use Mirasvit\Helpdesk\Model\TicketFactory;
use Magento\Framework\App\ResourceConnection;

class StringUtil
{

    protected TicketFactory $ticketFactory;

    protected ResourceConnection $resourceConnection;
    
    /**
     *  StringUtil constructor
     *  TicketFactory $ticketFactory
     *  ResourceConnection $resourceConnection
     */
    public function __construct(TicketFactory $ticketFactory,ResourceConnection $resourceConnection)
    {
      $this->ticketFactory  = $ticketFactory;
      $this->resourceConnection = $resourceConnection;   
    }

    /**
     * after execute add order code
     * @param \Mirasvit\Helpdesk\Helper\StringUtil $subject 
     * @return $subject 
     */
    public function aftergenerateTicketCode(
        \Mirasvit\Helpdesk\Helper\StringUtil $subject,
        $result
    ) {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('mst_helpdesk_ticket');
        $primaryKey = 'ticket_id';
        $lastAddedId = $connection->fetchRow("SELECT MAX(`{$primaryKey}`) as lastId FROM `{$tableName}`");
        if(!empty($lastAddedId['lastId'])){
            $lastCode = $this->ticketFactory->create()->load($lastAddedId['lastId'])->getCode();
            return $lastCode+1;
        }
        return $result;
    }
}