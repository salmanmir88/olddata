<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Plugin\Adminhtml\Mirasvit\Helpdesk\Helper;

use Magento\Backend\Model\Auth\Session;
use Mirasvit\Helpdesk\Model\TicketFactory;
use Dakha\CustomWork\Model\TicketHistroyFactory;
use Magento\User\Model\UserFactory;
use Mirasvit\Helpdesk\Model\StatusFactory;
use Mirasvit\Helpdesk\Model\PriorityFactory;

class Process
{
    protected Session $session;

    protected TicketFactory $ticketFactory;

    protected TicketHistroyFactory $ticketHistroy;

    protected UserFactory $userFactory;

    protected StatusFactory $statusFactory;

    protected PriorityFactory $priorityFactory;
    
    /**
     *  Process constructor
     *  @param Session $session 
     *  @param TicketFactory $ticketFactory
     *  @param TicketHistroyFactory $ticketHistroy
     *  @param UserFactory $userFactory
     *  @param StatusFactory $statusFactory
     *  @param PriorityFactory $priorityFactory
     */
    public function __construct(
        Session $session,
        TicketFactory $ticketFactory,
        TicketHistroyFactory $ticketHistroy,
        UserFactory $userFactory,
        StatusFactory $statusFactory,
        PriorityFactory $priorityFactory
    )
    {
      $this->session = $session;
      $this->ticketFactory  = $ticketFactory;
      $this->ticketHistroy = $ticketHistroy;
      $this->userFactory = $userFactory;
      $this->statusFactory = $statusFactory;
      $this->priorityFactory = $priorityFactory;   
    }

    
    /**
     * before execute add admin user name add post request
     * @param \Mirasvit\Helpdesk\Helper\Process $subject 
     * @return $subject 
     */
    public function beforeCreateOrUpdateFromBackendPost(
        \Mirasvit\Helpdesk\Helper\Process $subject,
        $data, 
        $user
    ) {
        if(!empty($data['ticket_id'])){
           $ticket = $this->ticketFactory->create()->load($data['ticket_id']); 
           $data['histroy_id'] = $this->beforeAddHistroy($ticket);
        }
        return [$data,$user];
    } 
    /**
     * after execute add admin user name add post request
     * @param \Mirasvit\Helpdesk\Helper\Process $subject 
     * @return $subject 
     */
    public function aftercreateOrUpdateFromBackendPost(
        \Mirasvit\Helpdesk\Helper\Process $subject,
        $result
    ) {
        
        if(empty($result->getAdminUser())){ 
           $this->ticketFactory->create()->load($result->getTicketId())
                         ->setAdminUser($this->session->getUser()->getUsername())
                         ->save();   
        }
        if($result->getAssignId()){ 
           $this->ticketFactory->create()->load($result->getTicketId())
                         ->setUserId($result->getAssignId())
                         ->save();   
        }
        $this->afterAddHistroy($result);
        return $result;
    }

    /**
     * Add histroy
     * @param $result 
     */
    public function beforeAddHistroy($ticket){
        $assignName = '';
        $subAssignName = '';
        $statusName = '';
        $priorityName = '';

        if($ticket->getUserId()){
          $userLoad = $this->userFactory->create()->load($ticket->getUserId());
          $assignName = $userLoad->getUsername();
        }
        if($ticket->getSubAssign()){
          $userLoad = $this->userFactory->create()->load($ticket->getSubAssign());
          $subAssignName = $userLoad->getUsername();
        }
        if($ticket->getStatusId()){
          $statusLoad = $this->statusFactory->create()->load($ticket->getStatusId());
          $statusName = $statusLoad->getName();
        }
        if($ticket->getPriorityId()){
          $priorityLoad = $this->priorityFactory->create()->load($ticket->getPriorityId());
          $priorityName = $priorityLoad->getName();
        } 
        $content =  '<div>';
        $content .= '<div class="updateby">Updated by: '.$this->session->getUser()->getUsername().'</div>';
        $content .= '<div class="updateby">Before Assignee: '.$assignName.'</div>';  
        $content .= '<div class="updateby">Before Sub Assignee: '.$subAssignName.'</div>';
        $content .= '<div class="updateby">Before Status: '.$statusName.'</div>';
        $content .= '<div class="updateby">Before Priority: '.$priorityName.'</div>';
        if($ticket->getReply()){
        $content .= '<div class="updateby">Before Reply: '.$ticket->getReply().'</div>';
        }
        $content .= '</div>';
        $histroyModel = $this->ticketHistroy->create()
                             ->setTicketId($ticket->getTicketId())
                             ->setTicketHistroy($content)
                             ->save(); 
        return $histroyModel->getId();                     
    }

    /**
     * Add histroy
     * @param $result 
     */
    public function afterAddHistroy($result){
        $assignName = '';
        $subAssignName = '';
        $statusName = '';
        $priorityName = '';
        if($result->getAssignId()){
          $userLoad = $this->userFactory->create()->load($result->getAssignId());
          $assignName = $userLoad->getUsername();
        }
        if($result->getSubAssign()){
          $userLoad = $this->userFactory->create()->load($result->getSubAssign());
          $subAssignName = $userLoad->getUsername();
        }
        if($result->getStatusId()){
          $statusLoad = $this->statusFactory->create()->load($result->getStatusId());
          $statusName = $statusLoad->getName();
        }
        if($result->getPriorityId()){
          $priorityLoad = $this->priorityFactory->create()->load($result->getPriorityId());
          $priorityName = $priorityLoad->getName();
        } 
        $content =  '<div>';
        $content .= '<div class="updateby">Updated by: '.$this->session->getUser()->getUsername().'</div>';
        $content .= '<div class="updateby">After Assignee: '.$assignName.'</div>';  
        $content .= '<div class="updateby">After Sub Assignee: '.$subAssignName.'</div>';
        $content .= '<div class="updateby">After Status: '.$statusName.'</div>';
        $content .= '<div class="updateby">After Priority: '.$priorityName.'</div>';
        if($result->getReply()){
        $content .= '<div class="updateby">After Reply: '.$result->getReply().'</div>';
        }
        $content .= '</div>';
        if($result->getHistroyId())
        {
          $histroyModel = $this->ticketHistroy->create()->load($result->getHistroyId());
          $beforeContent = $histroyModel->getTicketHistroy();
          $beforeContent .= $content;
          $histroyModel->setTicketHistroy($beforeContent)->save();   
        }else{
          $this->ticketHistroy->create()
                             ->setTicketId($result->getTicketId())
                             ->setTicketHistroy($content)
                             ->save(); 
        } 
    }
}