<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\ViewModel\Mirasvit\Helpdesk\Block\Ticket;

use Mirasvit\Helpdesk\Model\ResourceModel\Satisfaction\CollectionFactory as SatisfactionCollection;

class View extends \Magento\Framework\DataObject implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var SatisfactionCollection
     */
    protected $satisfactionCollection;

    /**
     * View constructor.
     * @param SatisfactionCollection $satisfactionCollection
     * 
     */
    public function __construct(SatisfactionCollection $satisfactionCollection)
    {
        $this->satisfactionCollection = $satisfactionCollection;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getSatisfactionCheck($ticketId)
    {
       return $this->satisfactionCollection->create()->addFieldToFilter('main_table.ticket_id',['in'=>[$ticketId]])->getSize();
    }
}