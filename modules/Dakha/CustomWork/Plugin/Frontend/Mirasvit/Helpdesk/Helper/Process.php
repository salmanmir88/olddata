<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Plugin\Frontend\Mirasvit\Helpdesk\Helper;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\Registry;
use Mirasvit\Helpdesk\Model\TicketFactory;

class Process
{
    protected OrderRepositoryInterface $orderRepository;

    protected TicketFactory $ticketFactory;
    
    /**
     *  Postmessage constructor
     *  OrderRepositoryInterface $orderRepository 
     *  TicketFactory $ticketFactory
     */
    public function __construct(OrderRepositoryInterface $orderRepository,TicketFactory $ticketFactory)
    {
      $this->orderRepository = $orderRepository;
      $this->ticketFactory  = $ticketFactory;   
    }

    /**
     * before execute add order increment id add post request
     * @param \Mirasvit\Helpdesk\Controller\Ticket\Postmessage $subject 
     * @return $subject 
     */
    public function afterCreateFromPost(
        \Mirasvit\Helpdesk\Helper\Process $subject,
        $result
    ) {
        if(!empty($result->getOrderId())){
           $incrementId = $this->orderRepository->get($result->getOrderId())->getIncrementId();
           $this->ticketFactory->create()->load($result->getTicketId())
                         ->setOrderNumber($incrementId)
                         ->save();   
        }
        if(empty($result->getAdminUser())){
           $this->ticketFactory->create()->load($result->getTicketId())
                         ->setAdminUser('customer')
                         ->save();   
        }
        return $result;
    }
}