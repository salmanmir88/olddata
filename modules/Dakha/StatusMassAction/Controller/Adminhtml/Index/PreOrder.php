<?php
/**
 * Copyright © StatusMassAction All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\StatusMassAction\Controller\Adminhtml\Index;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class PreOrder extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    
    const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;


    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $request = $this->getRequest();
        $orderIds = $request->getPost('selected', []);
        $orderIncIds = ''; 
        if(isset($request->getPost()['search']) && $request->getPost()['search'])
        {
            $orderIds = explode(',',$request->getPost()['search']);
            $orderIncIds = explode(',',$request->getPost()['search']);
        }
        
        if (empty($orderIds)) {
            $this->getMessageManager()->addErrorMessage(__('No orders found.'));
            return $this->_redirect('sales/order/index');
        }
        
        if($orderIncIds)
        {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('increment_id', ['in' => $orderIncIds]);
        }else{
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);            
        }
        

        try {
            
            //Add you logic

            foreach ($orderCollection as $order) { 
                    $order->setStatus('ship_via_courier');
                    $order->save();
            }

        } catch (\Exception $e) {
            $message = "An unknown error occurred while changing selected orders.";
            $this->getMessageManager()->addErrorMessage(__($message));
        }

        return $this->_redirect('sales/order/index');
    }
}
