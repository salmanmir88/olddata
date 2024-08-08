<?php
/**
 * Copyright © Developerswing All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Developerswing\OrderTracking\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
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
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set("Kpop Store: Track Your Order or Shipment");
        $resultPage->getConfig()->setDescription("Track your order or shipment.Thank you for shopping online with kpopia shop! Enter order ID to track the status of order. Easy returns & free shipping on orders .");
        return $resultPage;
    }
}

