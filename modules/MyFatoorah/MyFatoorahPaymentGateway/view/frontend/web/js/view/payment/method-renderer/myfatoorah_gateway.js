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

            var urlCode = 'myfatoorah';
            var checkoutConfig = window.checkoutConfig.payment.myfatoorah_gateway;

            var mfData;

            var mfError = checkoutConfig.mfError;
            return Component.extend({
                redirectAfterPlaceOrder: false,
                defaults: {
                    template: 'MyFatoorah_MyFatoorahPaymentGateway/payment/form'
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
                    return 'myfatoorah_gateway';
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
                    mfData = 'gateway=' + jQuery("input[name=mf_payment]:checked").val();
                    self.placeOrder();
                    return;
                },
                getGateways: function () {
                    if (mfError) {
                        self.messageContainer.addErrorMessage({
                            message: mfError
                        });
                        $("#mfSubmitGateway").attr("disabled", "disabled");
                        return;
                    }
                    
                    $.each(checkoutConfig.gateways, function (key, value) {
                        
                        if (key === 'myfatoorah') {
                            var url = 'https://portal.myfatoorah.com/imgs/logo-myfatoorah-sm-blue.png';
                        } else {
                            var url = 'https://portal.myfatoorah.com/imgs/payment-methods/' + key + '.png';
                        }
                        
                    if(key=='kn'){
                         var url = 'https://portal.myfatoorah.com/imgs/payment-methods/md.png';
                         value = 'uaecc';
                         jQuery('#mf_payment').append('<div class="mf-div"><input type="radio" name="mf_payment" id="myfatoorah_kn" class="radio mf-radio" value="uaecc"/><label for="myfatoorah_' + key + '"><img src="' + url + '"  class="mf-img" alt="' + value + '"/></label></div>');
                    }else{
                         jQuery('#mf_payment').append('<div class="mf-div"><input type="radio" name="mf_payment" id="myfatoorah_' + key + '" class="radio mf-radio" value="' + key + '"/><label for="myfatoorah_' + key + '"><img src="' + url + '"  class="mf-img" alt="' + value + '"/></label></div>');   
                    }
                         jQuery("input[name=mf_payment]:first").attr('checked', true);

                    });

                    if (jQuery("input[name=mf_payment]").length === 1) {
                        jQuery("input[name=mf_payment]").parent().hide();
                    }
                }
            });
        }
);
