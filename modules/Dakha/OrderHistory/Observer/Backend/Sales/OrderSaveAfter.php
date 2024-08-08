<?php
/**
 * Copyright © OrderHistory All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\OrderHistory\Observer\Backend\Sales;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Psr\Log\LoggerInterface;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HistoryFactory $historyFactory,
        OrderStatusHistoryRepositoryInterface $historyRepository,
        \Magento\Backend\Model\Auth\Session $authSession,
        LoggerInterface $logger
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->logger = $logger;
        $this->authSession = $authSession;
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
        $order = $observer->getEvent()->getOrder();
        $this->addCommentToOrder($order);
    }

    /**
     * add sales order status history data
     *
     * @param $order
     * @return void
     */
    public function addCommentToOrder($order)
    {
        /** @var OrderStatusHistoryInterface $history */
        $history  = $this->historyFactory->create();
        $userName = $this->authSession->getUser()->getUsername();
        $message  = 'This order status changed by'.'('.$userName.')';
        $status   = 'pending';
        $history->setParentId($order->getId())
            ->setComment($message)
            ->setIsCustomerNotified(1)
            ->setEntityName('order')
            ->setStatus($order->getStatus());
        try {
            $this->historyRepository->save($history);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}

