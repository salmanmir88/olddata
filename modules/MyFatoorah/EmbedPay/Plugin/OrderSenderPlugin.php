<?php

namespace MyFatoorah\EmbedPay\Plugin;

use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order;

class OrderSenderPlugin {

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function aroundSend(OrderSender $subject, callable $proceed, Order $order, $forceSyncMode = false) {
        $payment = $order->getPayment()->getMethodInstance()->getCode();

        $isMFcode = $payment === 'myfatoorah_payment' || $payment === 'myfatoorah_gateway' || $payment === 'embedpay';
        if ($isMFcode && $order->getState() === Order::STATE_PENDING_PAYMENT) {
            return false;
        }

        return $proceed($order, $forceSyncMode);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
