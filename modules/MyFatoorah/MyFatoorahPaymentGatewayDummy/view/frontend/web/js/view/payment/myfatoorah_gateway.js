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
                type: 'myfatoorah_gatewaydummy',
                component: 'MyFatoorah_MyFatoorahPaymentGatewayDummy/js/view/payment/method-renderer/myfatoorah_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
