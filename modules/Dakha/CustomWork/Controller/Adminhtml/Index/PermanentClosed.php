<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface;
use Mirasvit\Helpdesk\Model\TicketFactory;

class PermanentClosed extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var  TicketFactory
     */
    protected $ticketFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param TicketFactory $ticketFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        TicketFactory $ticketFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->ticketFactory = $ticketFactory;
        parent::__construct($context);
    }
    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $ticketId = $this->getRequest()->getParam('ticket_id');
            $this->ticketFactory->create()->load($ticketId)
                 ->setPermanentClosed(1)
                 ->save();
            $this->messageManager->addSuccessMessage('Successfully permanent closed');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());            
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('helpdesk/ticket/edit/', ['id' => $ticketId]);
        return $resultRedirect;
    }
}
