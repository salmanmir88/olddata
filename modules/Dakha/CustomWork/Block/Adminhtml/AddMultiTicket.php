<?php

namespace Dakha\CustomWork\Block\Adminhtml;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Mirasvit\Helpdesk\Model\ResourceModel\Status\CollectionFactory as StatusCollectionFactory;
use Mirasvit\Helpdesk\Model\ResourceModel\Priority\CollectionFactory as PriorityCollectionFactory; 
use Mirasvit\Helpdesk\Helper\Html as HelpdeskHtml;
use Dakha\CustomWork\Helper\Data as ConfigHelper;
use Magento\Framework\Data\Form\FormKey;

/**
 * Class AddMultiTicket
*/
class AddMultiTicket extends \Magento\Backend\Block\Template
{
    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var StatusCollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * @var PriorityCollectionFactory
     */
    protected $priorityCollectionFactory;

    /**
     * @var HelpdeskHtml
     */
    protected $helpdeskHtml;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * 
     * @var FormKey 
     */
    protected $formKey;

   /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param StatusCollectionFactory $statusCollectionFactory
     * @param PriorityCollectionFactory $priorityCollectionFactory
     * @param HelpdeskHtml $helpdeskHtml
     * @param ConfigHelper $configHelper
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry, 
        OrderCollectionFactory $orderCollectionFactory,
        StatusCollectionFactory $statusCollectionFactory,
        PriorityCollectionFactory $priorityCollectionFactory,
        HelpdeskHtml $helpdeskHtml,
        ConfigHelper $configHelper,
        FormKey $formKey,
        array $data = []
    )
    {
        $this->coreRegistry = $registry;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->priorityCollectionFactory = $priorityCollectionFactory;
        $this->helpdeskHtml = $helpdeskHtml;
        $this->configHelper = $configHelper;
        $this->formKey =  $formKey;
        parent::__construct($context, $data);
    }

  /**
   * get order ids 
   */
  public function getOrderIds()
  {
    $request = $this->getRequest();
    $orderIds = $request->getPost('selected', []);
    $orderIncIds = ''; 
    if(isset($request->getPost()['search']) && $request->getPost()['search'])
    {
        $orderIds = explode(',',$request->getPost()['search']);
        $orderIncIds = explode(',',$request->getPost()['search']);
    }
    if($orderIncIds)
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('increment_id', ['in' => $orderIncIds]);
    }else{
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);            
    }
    $inrementIdsArr = [];
     foreach ($orderCollection as $order) { 
          $inrementIdsArr[] = $order->getIncrementId();
    }

    if(!empty($inrementIdsArr)){
       return implode(",",$inrementIdsArr);
    } 
    return $inrementIdsArr;
  }

  /**
   * get ticket status
   * @return StatusCollectionFactory
   */
  public function getStatus()
  {
    return $this->statusCollectionFactory->create();
  }

  /**
   * get ticket priority
   * @return PriorityCollectionFactory
   */
  public function getPriority()
  {
    return $this->priorityCollectionFactory->create();
  }

  /**
   * get assignee
   * @return array
   */
  public function getAssignee()
  {
    return $this->helpdeskHtml->getAdminOwnerOptionArray();
  }

  /**
   * get subjects
   * @return array
   */
  public function getSubjects()
  {
    return $this->configHelper->getSubjects();
  }
  
  /**
   * get formkey
   * @return string
   */
  public function getFormKey()
  {
    return $this->formKey->getFormKey();
  } 
}