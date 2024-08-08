<?php
/**
 * Copyright © est All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\OrderInvoiceGenerate\Observer\Backend\Sales;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundOrderInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Creditmemo\NotifierInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\RefundAdapterInterface;
use Magento\Sales\Model\Order\Validation\RefundOrderInterface as RefundOrderValidator;
use Psr\Log\LoggerInterface;

class OrderCreditmemoSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OrderStateResolverInterface
     */
    private $orderStateResolver;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var RefundAdapterInterface
     */
    private $refundAdapter;

    /**
     * @var CreditmemoDocumentFactory
     */
    private $creditmemoDocumentFactory;

    /**
     * @var RefundOrderValidator
     */
    private $validator;

    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var OrderConfig
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RefundOrder constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param OrderStateResolverInterface $orderStateResolver
     * @param OrderRepositoryInterface $orderRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param RefundAdapterInterface $refundAdapter
     * @param CreditmemoDocumentFactory $creditmemoDocumentFactory
     * @param RefundOrderValidator $validator
     * @param NotifierInterface $notifier
     * @param OrderConfig $config
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderStateResolverInterface $orderStateResolver,
        OrderRepositoryInterface $orderRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        RefundAdapterInterface $refundAdapter,
        CreditmemoDocumentFactory $creditmemoDocumentFactory,
        RefundOrderValidator $validator,
        NotifierInterface $notifier,
        OrderConfig $config,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderStateResolver = $orderStateResolver;
        $this->orderRepository = $orderRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->refundAdapter = $refundAdapter;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->validator = $validator;
        $this->notifier = $notifier;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $order = $creditmemo->getOrder();
        
            $creditmemo->setState(\Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED);
            //$order = $this->refundAdapter->refund($creditmemo, $order);
            $orderState = $this->orderStateResolver->getStateForOrder($order, []);
            $order->setState('closed');
            $order->setStatus('closed');
            $order->save();
    }
}