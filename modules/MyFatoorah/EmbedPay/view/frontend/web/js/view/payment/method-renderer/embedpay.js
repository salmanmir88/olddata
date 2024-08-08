/*browser:true*/
/*global define*/
define(
        [
            'mfSessionFile', // here the session.js file is mapped
            'jquery',
            'Magento_Checkout/js/view/payment/default',
            'mage/url'
        ],
        function (
                mfSessionFile,
                $,
                Component,
                url
                ) {
            'use strict';
            var self;

            var urlCode = 'myfatoorah_embedpay';
            var checkoutConfig = window.checkoutConfig.payment.embedpay;

            var mfData;

            var mfError = checkoutConfig.mfError;
            return Component.extend({
                redirectAfterPlaceOrder: false,
                defaults: {
                    template: 'MyFatoorah_EmbedPay/payment/form'
                },
                initialize: function () {
                    this._super();
                    self = this;
                },
                initObservable: function () {
                    this._super()
                            .observe([
                                'transactionResult'
                            ]);

                    return this;

                },
                getCode: function () {
                    return 'embedpay';
                },
                getData: function () {
                    return {
                        'method': this.item.method,
                        'additional_data': {
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
                placeOrderEmbed: function () {
                    if (mfError) {
                        return false;
                    }
                    $('body').loader('show');
                    myFatoorah.submit()
                            .then(function (response) {// On success
                                mfData = 'sid=' + response.SessionId;
                                self.placeOrder();
                            }, function (error) { // In case of errors
                                $('body').loader('hide');
                                self.messageContainer.addErrorMessage({
                                    message: error
                                });
                            });
                },
                getIFrame: function () {
                    if (mfError) {
                        self.messageContainer.addErrorMessage({
                            message: mfError
                        });
                        $("#mfSubmitEmbed").attr("disabled", "disabled");
                        return;
                    }

                    var mfConfig = {
                        countryCode: checkoutConfig.countryCode,
                        sessionId: checkoutConfig.sessionId,
                        cardViewId: "card-element",
                        // The following style is optional.
                        style: {
                            cardHeight: 145,
                            input: {
                                color: "black",
                                fontSize: "13px",
                                fontFamily: "sans-serif",
                                inputHeight: "32px",
                                inputMargin: "-1px",
                                borderColor: "c7c7c7",
                                borderWidth: "1px",
                                borderRadius: "0px",
                                boxShadow: "",
                                placeHolder: {
                                    holderName: "Name On Card",
                                    cardNumber: "Number",
                                    expiryDate: "MM / YY",
                                    securityCode: "CVV"
                                }
                            },
                            label: {
                                display: false,
                                color: "black",
                                fontSize: "13px",
                                fontFamily: "sans-serif",
                                text: {
                                    holderName: "Card Holder Name",
                                    cardNumber: "Card Number",
                                    expiryDate: "ExpiryDate",
                                    securityCode: "Security Code"
                                }
                            },
                            error: {
                                borderColor: "red",
                                borderRadius: "8px",
                                boxShadow: "0px"
                            }
                        }
                    };
                    myFatoorah.init(mfConfig);
                    window.addEventListener("message", myFatoorah.recievedMessage, false);
                }
            });
        }
);
