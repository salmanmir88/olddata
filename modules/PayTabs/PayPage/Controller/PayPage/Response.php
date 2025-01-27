<?php

// declare(strict_types=1);

namespace PayTabs\PayPage\Controller\Paypage;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use PayTabs\PayPage\Gateway\Http\Client\Api;
use PayTabs\PayPage\Gateway\Http\PaytabsCore2;

use function PayTabs\PayPage\Gateway\Http\paytabs_error_log;

/**
 * Class Index
 */
class Response extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;
    // protected $resultRedirect;
    private $paytabs;

    /**
     * @var Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    private $_orderSender;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    // protected $_logger;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
        // \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->pageFactory = $pageFactory;
        $this->_orderSender = $orderSender;
        // $this->_logger = $logger;
        // $this->resultRedirect = $context->getResultFactory();
        $this->paytabs = new \PayTabs\PayPage\Gateway\Http\Client\Api;
        new PaytabsCore2();
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            $pOrderId = $this->getRequest()->getParam('p', null);
            paytabs_error_log("Paytabs: no post back data received in callback = ".$pOrderId);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($pOrderId);
            if (!$order) 
            {
                paytabs_error_log("Paytabs: Order is missing, Order param = [{$pOrderId}]");
                return;
            }

            $this->setNewStatus($order, Order::STATE_CANCELED);
            $order->save();


            return;
        }

        // Get the params that were passed from our Router
        $pOrderId = $this->getRequest()->getParam('p', null);

        // PT
        // PayTabs "Invoice ID"
        $transactionId = $this->getRequest()->getParam('tranRef', null);

        $resultRedirect = $this->resultRedirectFactory->create();

        //

        if (!$pOrderId || !$transactionId) {
            paytabs_error_log("Paytabs: OrderId/TransactionId data did not receive in callback");
            return;
        }

        //

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($pOrderId);

        if (!$order) {
            paytabs_error_log("Paytabs: Order is missing, Order param = [{$pOrderId}]");
            return;
        }

        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethodInstance();

        $paymentSuccess =
            $paymentMethod->getConfigData('order_success_status') ?? Order::STATE_PROCESSING;
        $paymentFailed =
            $paymentMethod->getConfigData('order_failed_status') ?? Order::STATE_CANCELED;

        $sendInvoice = $paymentMethod->getConfigData('send_invoice') ?? false;

        $ptApi = $this->paytabs->pt($paymentMethod);

        $verify_response = $ptApi->verify_payment($transactionId);
        
        $success = $verify_response->success;
        $res_msg = $verify_response->message;
        $orderId = @$verify_response->reference_no;
        $transaction_ref = @$verify_response->transaction_id;
       
        /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($verify_response,true));*/


        if (!$success) {
            paytabs_error_log("Paytabs Response: Payment verify failed [$res_msg] for Order {$pOrderId}");
           try {

                $payment->deny();
             } catch (\Exception $e) {
                   
             }
            if ($paymentFailed != Order::STATE_CANCELED) {
                $this->setNewStatus($order, $paymentFailed);
            }
            $order->save();

            $this->messageManager->addErrorMessage($res_msg);
            $resultRedirect->setPath('checkout/onepage/failure');
            return $resultRedirect;
        }

        if ($pOrderId != $orderId) {
            paytabs_error_log("Paytabs Response: Order reference number is mismatch, Order = [{$pOrderId}], ReferenceId = [{$verify_response->reference_no}] ");
            $this->messageManager->addWarningMessage('Order reference number is mismatch');
            $resultRedirect->setPath('checkout/onepage/failure');
            return $resultRedirect;
        }


        if (Api::hadPaid($order)) {
            $this->messageManager->addWarningMessage('A previous paid amount detected for this Order, please contact us for more information');
        }

        // PayTabs "Transaction ID"
        $paymentAmount = $verify_response->cart_amount;
        $paymentCurrency = $verify_response->cart_currency;

        $payment
            ->setTransactionId($transaction_ref)
            ->setLastTransId($transaction_ref)
            ->setIsTransactionClosed(true)
            ->setShouldCloseParentTransaction(true)
            ->setAdditionalInformation("payment_amount", $paymentAmount)
            ->setAdditionalInformation("payment_currency", $paymentCurrency)
            ->save();

        $payment->accept();

        $payment->capture();

        if ($sendInvoice) {
            $payment->registerCaptureNotification($paymentAmount, true)->save();

            $invoice = $payment->getCreatedInvoice();
            if ($invoice && !$order->getEmailSent()) {
                $this->_orderSender->send($order);
                $order->addStatusHistoryComment(
                    __('You notified customer about invoice #%1.', $invoice->getIncrementId())
                )
                    ->setIsCustomerNotified(true)
                    ->save();
            }
        }

        $transType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
        $transaction = $payment->addTransaction($transType, null, false);
        $transaction
            ->setIsClosed(true)
            ->setParentTxnId(null)
            ->save();


        if ($paymentSuccess != Order::STATE_PROCESSING) {
            $this->setNewStatus($order, $paymentSuccess);
        }
        $order->save();

        $this->messageManager->addSuccessMessage($res_msg);
        $resultRedirect->setPath('checkout/onepage/success');

        return $resultRedirect;

        // return $this->pageFactory->create();
    }

    //

    public function setNewStatus($order, $newStatus)
    {
        $order->setState($newStatus)->setStatus($newStatus);
        $order->addStatusToHistory($newStatus, "Order was set to '$newStatus' as in the admin's configuration.");
    }
}

/**
 * move CRSF verification to Plugin
 * compitable with old Magento version >=2.0 && <2.3
 * compitable with PHP version 5.6
 */
