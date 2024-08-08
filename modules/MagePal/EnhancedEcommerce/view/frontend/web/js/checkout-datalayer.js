/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/view/payment',
    'Magento_Checkout/js/model/step-navigator'
], function ($, _, quote, payment, stepsNavigator) {

    function hasPath(obj, path)
    {
        var hasKey = true;

        for (var i = 0, path = path.split('.'), len = path.length; i<len; i++) {
            if (_.has(obj, [path[i]])) {
                obj = obj[path[i]];
            } else {
                hasKey = false;
                break;
            }
        }

        return hasKey;
    }

    var CheckoutBehavior = new function () {
        this.title = 'Shipping';
        this.index = 1;
        this.code = 'shipping';
        this.availableSteps = {};

        this.setTitle = function (title) {
            this.title = title;
            return this;
        };

        this.getTitle = function () {
            return this.title;
        };

        this.setCode = function (code) {
            this.code = code;
            return this;
        };

        this.getCode = function () {
            return this.code;
        };

        this.setAvailableSteps = function (availableSteps) {
            this.availableSteps = availableSteps;
            return this;
        };

        this.getAvailableSteps = function () {
            return this.availableSteps;
        };

        this.isOnStep = function (step) {
            var object = this.getAvailableSteps();
            return (_.isObject(object) && _.has(object, step) && object[step] === this.getIndex());
        };

        this.setIndex = function (index) {
            this.index = index;
            return this;
        };

        this.getIndex = function () {
            return this.index;
        };
    };

    var loadDataLayer = function (dataLayer) {
        var _dataLayer = {};
        _.each(dataLayer, function (item) {
            _dataLayer = $.extend(true, _dataLayer, item);
        });

        return _dataLayer;
    };

    var initTrigger = function (dataLayer, config) {
        var $body = $('body');
        $body.on('mpCheckoutShippingStepValidation', function (event, isFormValid, errors) {
            if (isFormValid) {
                dataLayer.push({'event': 'checkoutShippingStepCompleted'});
            } else {
                dataLayer.push({
                    'event': 'checkoutShippingStepFailed',
                    'checkout': {
                        'shipping_errors': errors
                    }
                });
            }
        });

        $body.on('mpCheckoutPaymentStepValidation', function (event, isFormValid, errors) {
            if (isFormValid) {
                dataLayer.push({'event': 'checkoutPaymentStepCompleted'});
            } else {
                dataLayer.push({
                    'event': 'checkoutPaymentStepFailed',
                    'checkout': {
                        'payment_errors': errors
                    }
                });
            }
        });

        $body.on('mpCheckoutEmailValidation', function (event, emailExist) {
            //include once
            let dlObject = loadDataLayer(dataLayer);

            if (!hasPath(dlObject, 'checkout.email_exist') || dlObject.checkout.email_exist !== emailExist) {
                dataLayer.push({
                    'event': 'checkoutEmailValidation',
                    'checkout': {
                        'email_exist': emailExist
                    }
                });
            }
        });

        $body.on('mpCheckoutItemQtyChanged', function (event, itemId, qty, qtyOriginal) {
            if (_.has(config, 'products') && _.isArray(config.products)) {
                _.each(config.products, function (item) {
                    if (parseInt(item.item_id) === itemId) {
                        var eventName = (qty > qtyOriginal) ? 'addToCart' : 'removeFromCart';

                        if (eventName === 'addToCart') {
                            item.quantity = qty - qtyOriginal;
                            dataLayer.push({
                                'event': eventName,
                                'ecommerce': {
                                    'currencyCode': config.currencyCode,
                                    'add': {
                                        'products': [item]
                                    }
                                }
                            });
                        } else {
                            item.quantity = qtyOriginal - qty;
                            dataLayer.push({
                                'event': eventName,
                                'ecommerce': {
                                    'currencyCode': config.currencyCode,
                                    'remove': {
                                        'products': [item]
                                    }
                                }
                            });
                        }
                    }
                });
            }
        });

        $body.on('mpCheckoutItemRemoved', function (event, itemId, qty) {
            if (_.has(config, 'products') && _.isArray(config.products)) {
                _.each(config.products, function (item) {
                    if (parseInt(item.item_id) === itemId) {
                        item.quantity = qty;
                        dataLayer.push({
                            'event': 'removeFromCart',
                            'ecommerce': {
                                'currencyCode': config.currencyCode,
                                'remove': {
                                    'products': [item]
                                }
                            }
                        });
                    }
                });
            }
        });
    };


    return function (config) {
        var dataLayer = window[config.dataLayerName];
        CheckoutBehavior.setAvailableSteps(config.checkoutBehaviorSteps);

        initTrigger(dataLayer, config)

        if (config.isOneStepCheckout === 1) {
            notifyCheckoutStep(1, 'Shipping', 'shipping');
        } else {
            $(window).bind('hashchange', function () {
                updateCheckoutStep();
            });

            var pageLoadObserve = stepsNavigator.steps.subscribe(function (value) {
                if (value.length) {
                    _.each(value, function (element) {
                        if (element.isVisible()) {
                            pageLoadObserve.dispose();
                            //run on page load
                            updateCheckoutStep();
                        }
                    });
                }
            });
        }

        function updateCheckoutStep()
        {
            var index = stepsNavigator.getActiveItemIndex();
            var steps = stepsNavigator.steps();
            var step = {};

            if (steps.length && index < steps.length) {
                step = steps[index];

                if (_.isObject(step) && _.has(step, 'code')) {
                    notifyCheckoutStep(index + 1, step.title ? step.title : step.code, step.code);
                }
            }

            updateShippingMethod(quote.shippingMethod());
            updatePaymentMethod(quote.paymentMethod());
        }

        function notifyCheckoutStep(index, title, code)
        {
            CheckoutBehavior.setIndex(index);
            CheckoutBehavior.setTitle(title);
            CheckoutBehavior.setCode(code);

            var dlUpdate = {
                'event': 'checkout',
                'ecommerce': {
                    'checkout': {
                        'actionField': {
                            'step': index,
                            'option': title
                        }
                    }
                }
            };

            //include once
            let dlObject = loadDataLayer(dataLayer);

            if (!hasPath(dlObject, 'ecommerce.checkout.actionField.step')
                || !hasPath(dlObject, 'ecommerce.checkout.actionField.option')
                || dlObject.ecommerce.checkout.actionField.step !== index
                || dlObject.ecommerce.checkout.actionField.option !== title
            ) {
                var products = config.products;

                $("body").trigger("mpCheckout", [index, title, code, products, dataLayer]);

                if (!hasPath(dlObject, 'ecommerce.checkout.products')) {
                    dlUpdate.ecommerce.checkout.products = products;
                    dlUpdate.ecommerce.currencyCode = config.currencyCode;
                }

                dataLayer.push(dlUpdate);
            }

        }

        quote.shippingMethod.subscribe(function (value) {
            updateShippingMethod(value);
        });

        quote.paymentMethod.subscribe(function (value) {
            updatePaymentMethod(value);
        });

        function updateShippingMethod(object)
        {
            if (_.isObject(object)
                && _.has(object, 'method_title')
                && _.has(object, 'carrier_title')
            ) {
                var option = '';

                if (object.method_title && object.carrier_title) {
                    option = object.method_title + ' - ' + object.carrier_title;
                }

                //include once
                let dlObject = loadDataLayer(dataLayer);

                if (CheckoutBehavior.isOnStep('shipping') && option) {
                    onCheckoutOption(CheckoutBehavior.getIndex(), option);

                    if (!hasPath(dlObject, 'checkout.shipping_method.title')
                        || dlObject.checkout.shipping_method.title !== option
                    ) {
                        dataLayer.push({
                            'event': 'shippingMethodAdded',
                            'checkout': {
                                'shipping_method': {
                                    'title': option,
                                    'amount': object.amount,
                                    'carrier_code': object.carrier_code,
                                    'carrier_title': object.carrier_title,
                                    'method_code': object.method_code,
                                    'method_title': object.method_title,
                                    'price_excl_tax': object.price_excl_tax,
                                    'price_incl_tax': object.price_incl_tax
                                }
                            }
                        });
                    }
                }
            }
        }

        function updatePaymentMethod(object)
        {
            if (_.isObject(object) && _.has(object, 'method')) {
                //include once
                let dlObject = loadDataLayer(dataLayer);

                if (config.isOneStepCheckout === 1 || (object.method &&
                    (CheckoutBehavior.isOnStep('payment') || window.location.hash.substr(1) === 'payment'))
                ) {
                    onCheckoutOption(CheckoutBehavior.getIndex(), object.method);

                    let title = _.has(object, 'title') ? object.title : object.method;

                    if (title && (!hasPath(dlObject, 'checkout.payment_method.method')
                        || dlObject.checkout.payment_method.method !== object.method)
                    ) {
                        dataLayer.push({
                            'event': 'paymentMethodAdded',
                            'checkout': {
                                'payment_method': {
                                    'title': title,
                                    'method': object.method
                                }
                            }
                        });
                    }
                }
            }
        }

        function onCheckoutOption(step, checkoutOption)
        {
            let dlObject = loadDataLayer(dataLayer);

            if (!hasPath(dlObject, 'ecommerce.checkout_option.actionField.step')
                || !hasPath(dlObject, 'ecommerce.checkout_option.actionField.option')
                || dlObject.ecommerce.checkout_option.actionField.step !== step
                || dlObject.ecommerce.checkout_option.actionField.option !== checkoutOption
            ) {
                $("body").trigger("mpCheckoutOption", [step, checkoutOption, dataLayer]);

                dataLayer.push({
                    'event': 'checkoutOption',
                    'ecommerce': {
                        'checkout_option': {
                            'actionField': {'step': step, 'option': checkoutOption}
                        }
                    }
                });
            }
        }
    }
});
