<?php
/**
 * Copyright © StatusMassAction All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Helpdesk\Model\TicketFactory;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\Helpdesk\Model\ResourceModel\Ticket\Collection as TicketCollection;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Message\ManagerInterface;

class SubmitTickets extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    
    /**
     * @var  TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var  AuthSession
     */
    protected $authSession;
    
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var TicketCollection
     */
    protected $ticketCollection;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param TicketFactory $ticketFactory
     * @param AuthSession $authSession
     * @param ResourceConnection $resourceConnection
     * @param TicketCollection $ticketCollection
     * @param OrderInterface $orderInterface
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        TicketFactory $ticketFactory,
        AuthSession $authSession,
        ResourceConnection $resourceConnection,
        TicketCollection $ticketCollection,
        OrderInterface $orderInterface,
        ManagerInterface $messageManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ticketFactory = $ticketFactory;
        $this->authSession = $authSession;
        $this->resourceConnection = $resourceConnection;
        $this->ticketCollection = $ticketCollection;
        $this->orderInterface = $orderInterface;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
       try {
            $request = $this->getRequest();
            $username = $this->authSession->getUser()->getUsername();
            $orderIds = explode(",", $request->getPost()['order_ids']);
            foreach($orderIds as $orderId)
            {
               $collection = $this->ticketCollection;
               $collection->addFieldToFilter('order_number',$orderId);

               if(count($collection) < 1 && !empty($this->getLastCode())){ 
                 $order = $this->orderInterface->loadByIncrementId($orderId);
                 $model = $this->ticketFactory->create();
                 $userIdDepartment = explode("_", $request->getPostValue()['owner']);
                 
                 $model->setCode($this->getLastCode())
                       ->setUserId($userIdDepartment[1])
                       ->setSubject($request->getPost()['subject'])
                       ->setPriorityId($request->getPost()['priority_id'])
                       ->setStatusId($request->getPost()['status_id'])
                       ->setDepartmentId($userIdDepartment[0])
                       ->setCustomerEmail($order->getCustomerEmail())
                       ->setCustomerName($order->getCustomerName())
                       ->setOrderId($order->getId())
                       ->setChannel('backend')
                       ->setOrderNumber($order->getIncrementId())
                       ->setAdminUser($username)
                       ->setStoreId(1)
                       ->save();
               } 
            }
           $this->messageManager->addSuccessMessage(__('Successfully ticket created selected orders'));
       } catch (Exception $e) { 
           $this->messageManager->addErrorMessage(__($e->getMessage()));
       }
       
       return $this->_redirect('sales/order/index');
    }

    /**
     * get lastcode
     * @return string
     * 
     */
    public function getLastCode()
    {
         $connection = $this->resourceConnection->getConnection();
         $tableName = $this->resourceConnection->getTableName('mst_helpdesk_ticket');
         $primaryKey = 'ticket_id';
         $lastAddedId = $connection->fetchRow("SELECT MAX(`{$primaryKey}`) as lastId FROM `{$tableName}`");
         if(!empty($lastAddedId['lastId'])){
             $lastCode = $this->ticketFactory->create()->load($lastAddedId['lastId'])->getCode();
             return $lastCode+1;
         }
         return "";
    }
}
