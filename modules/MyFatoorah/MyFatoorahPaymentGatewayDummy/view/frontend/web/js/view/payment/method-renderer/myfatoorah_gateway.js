/*browser:true*/
/*global define*/
define(
        [
            'jquery',
            'Magento_Checkout/js/view/payment/default',
            'mage/url'
        ],
        function (
                $,
                Component,
                url
                ) {
            'use strict';
            var self;

            var urlCode = 'dummymyfatoorah';
            var checkoutConfig = window.checkoutConfig.payment.myfatoorah_gatewaydummy;
            console.log(checkoutConfig);
            var mfData;

            var mfError = checkoutConfig.mfError;
            return Component.extend({
                redirectAfterPlaceOrder: false,
                defaults: {
                    template: 'MyFatoorah_MyFatoorahPaymentGatewayDummy/payment/form'
                },
                initialize: function () {
                    this._super();
                    self = this;
                },
                initObservable: function () {
                    this._super()
                            .observe([
                                'gateways',
                                'transactionResult'
                            ]);

                    return this;

                },
                getCode: function () {
                    return 'myfatoorah_gatewaydummy';
                },
                getData: function () {
                    return {
                        'method': this.item.method,
                        'additional_data': {
                            'gateways': this.gateways(),
                            'transaction_result': this.transactionResult()
                        }
                    };
                },
                validate: function () {
                    return true;
                },
                getTitle: function () {
                    return checkoutConfig.title;
                },
                getDescription: function () {
                    return checkoutConfig.description;
                },
                afterPlaceOrder: function () {
                    window.location.replace(url.build(urlCode + '/checkout/index?' + mfData));
                },
                placeOrderGateway: function () {
                    if (mfError) {
                        return false;
                    }
                    $('body').loader('show');
                    mfData = 'gateway=' + jQuery("input[name=dummy_mf_payment]:checked").val();
                    self.placeOrder();
                    return;
                },
                getGateways: function () {
                    if (mfError) {
                        self.messageContainer.addErrorMessage({
                            message: mfError
                        });
                        $("#dummy_mfSubmitGateway").attr("disabled", "disabled");
                        return;
                    }
                    
                    $.each(checkoutConfig.gateways, function (key, value) {

                        if (key === 'myfatoorah') {
                            var url = 'https://portal.myfatoorah.com/imgs/logo-myfatoorah-sm-blue.png';
                        } else {
                            var url = 'https://portal.myfatoorah.com/imgs/payment-methods/' + key + '.png';
                        }
                        
                        jQuery('#dummy_mf_payment').append('<div class="mf-div dmf_'+key+'"><input type="radio" name="dummy_mf_payment" id="myfatoorah_' + key + '" class="radio mf-radio" value="' + key + '"/><label for="myfatoorah_' + key + '"><img src="' + url + '"  class="mf-img" alt="' + value + '"/></label></div>');
                        jQuery("input[name=dummy_mf_payment]:first").attr('checked', true);

                    });

                    if (jQuery("input[name=dummy_mf_payment]").length === 1) {
                        jQuery("input[name=dummy_mf_payment]").parent().hide();
                    }
                }
            });
        }
);
