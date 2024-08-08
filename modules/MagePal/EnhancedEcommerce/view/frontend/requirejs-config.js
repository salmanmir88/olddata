var config = {
    map: {
        '*': {
            addToCartDataLayer: 'MagePal_EnhancedEcommerce/js/add-to-cart-datalayer',
            addToCartAjaxDataLayer: 'MagePal_EnhancedEcommerce/js/add-to-cart-ajax-datalayer',
            dataLayerShareComponent: 'MagePal_EnhancedEcommerce/js/shared-component',
            checkOutDataLayer: 'MagePal_EnhancedEcommerce/js/checkout-datalayer',
            enhancedDataLayer: 'MagePal_EnhancedEcommerce/js/datalayer'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'MagePal_EnhancedEcommerce/js/mixin/shipping-mixin': true
            },
            'CyberSource_Address/js/view/cybersource-shipping': {
                'MagePal_EnhancedEcommerce/js/mixin/shipping-mixin': true
            },
            'Magento_Checkout/js/view/payment/default': {
                'MagePal_EnhancedEcommerce/js/mixin/payment/default-mixin': true
            },
            'Magento_Checkout/js/view/form/element/email':{
                'MagePal_EnhancedEcommerce/js/mixin/view/form/element/email-mixin': true
            },
            'Aheadworks_OneStepCheckout/js/view/form/email':{
                'MagePal_EnhancedEcommerce/js/mixin/aheadworks/onestepcheckout/view/form/email-mixin': true
            },
            'Aheadworks_OneStepCheckout/js/view/sidebar/item-details/qty':{
                'MagePal_EnhancedEcommerce/js/mixin/aheadworks/onestepcheckout/view/sidebar/item-details/qty-mixin': true
            },
            'Aheadworks_OneStepCheckout/js/view/sidebar/item-details/actions':{
                'MagePal_EnhancedEcommerce/js/mixin/aheadworks/onestepcheckout/view/sidebar/item-details/actions-mixin': true
            }
        }
    }
};
