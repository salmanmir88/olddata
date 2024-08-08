define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'myfatoorah_gateway',
                component: 'MyFatoorah_MyFatoorahPaymentGateway/js/view/payment/method-renderer/myfatoorah_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
