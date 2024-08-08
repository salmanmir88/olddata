<?php 
namespace MyFatoorah\MyFatoorahPaymentGatewayDummy\Api;
 
 
interface MFOrderManagementInterface {

    /**
     * Returns payment status
     *
     * @api
     * @param int $cartId cart ID.     
     * @param int $billingAddressId billing Address ID.
     * @param string $gateway gateway.
     * @return mixed.
     */
     public function checkout($cartId, $billingAddressId, $gateway = null);
     
}

