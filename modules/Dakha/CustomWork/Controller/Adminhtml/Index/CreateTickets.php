<?php
/**
 * Copyright © StatusMassAction All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class CreateTickets extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    
    const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
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
      
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
