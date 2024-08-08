<?php
/**
 * Copyright © Dotsqures All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dotsqures\CustomInvoiceButton\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;
 
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;
 
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;

    protected $_invoiceSender;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $resultRedirectFactory;

    protected $messageManager;

    protected $order;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory

    )
    {
        $this->order = $order;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_invoiceSender = $invoiceSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->_messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_orderRepository->get($orderId);
        if($order->canInvoice()) {
            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            //$order = $this->order;
            $order->setState('processing');
            $order->setStatus('fetchr_held');
            $order->addStatusToHistory($order->getStatus(), 'Order processed successfully with reference');
            $order->save();
            $transactionSave = $this->_transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            $this->_invoiceSender->send($invoice);
            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->save();
            $this->_messageManager->addSuccess(__("Invoice created Successfully"));
            $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
            return $resultRedirect;
        }
        $this->_messageManager->addNotice(__("Invoice Already Created"));
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        return $resultRedirect;
    }
}

