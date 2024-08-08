/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

define([
    'uiClass',
    'jquery',
    'underscore',
    'jquery/jquery-storageapi'
], function (Component, $, _) {
    'use strict';

    return Component.extend({
        productCollection: {},
        config:{},
        setConfig: function (config) {
            this.config = config;
            return this;
        },
        /**
         * load data layer
         * @return object;
         */
        loadDataLayer: function () {
            var dataLayer = {};
            _.each(this.getDataLayerObject(), function (item, key) {
                dataLayer = $.extend(true, dataLayer, item);
            });

            return dataLayer;
        },
        /**
         * Get data layer object
         * @return {*|*[]}
         */
        getDataLayerObject: function () {
            window[this.config.dataLayerName] = window[this.config.dataLayerName] || [];
            return window[this.config.dataLayerName];
        },
        /**
         * Get local store data
         * @return {*}
         */
        getStorageData: function () {
            try {
                return $.initNamespaceStorage('magepal-enhanced-ecommerce').localStorage;
            } catch (e) {
                return null;
            }
        },
        /**
         * Get local store data
         * @return {*}
         */
        getProductClickStorageData: function () {
            var result = {};

            try {
                var storage = this.getStorageData();
                /**
                 * Product click format
                 * {pid: productId, sku: product.id, list: list.list_type, position: product.position}
                 */
                if (storage) {
                    result =  storage.get('product-click');
                }
            } catch (e) {
            }

            return result;
        },
        /**
         * Add click position to data
         * @param ecommerceData
         * @return {*}
         */
        processData: function (ecommerceData) {
            var storedData = this.getProductClickStorageData();

            _.each(ecommerceData, function (data) {
                if (_.has(data, 'parent_sku')) {
                    if (_.isObject(storedData)
                        && _.has(storedData, 'sku')
                        && _.has(storedData, 'position')
                        && data.parent_sku === storedData.sku
                    ) {
                        data.position = storedData.position;
                    }
                }
            });

            return ecommerceData;
        },
        /**
         * Push Data to data layer
         * @param data
         */
        pushAddToCartDataLayer: function (data) {
            var dataLayer = this.getDataLayerObject();
            $("body").trigger("mpItemAddToCart", [data, dataLayer]);
            dataLayer.push({
                'event': 'addToCart',
                'ecommerce': {
                    'currencyCode': this.config.currencyCode,
                    'add': {
                        'products': data
                    }
                },
                'cart': {
                    'add': {
                        'products': data
                    }
                }
            });
        },
        /**
         * Add, remove, change qty data layer push
         * @param data
         */
        pushAddRemoveItemDataLayer: function (data) {
            if (_.isObject(data) && _.has(data, 'cartItems')) {
                var ecommerce;
                var cartGenericLayer = {};
                var dataLayer = this.getDataLayerObject();
                var self = this;

                _.each(data.cartItems, function (cartItem) {
                    if (_.has(cartItem, 'ecommerce')) {
                        ecommerce = cartItem.ecommerce;
                        ecommerce.currencyCode = self.config.currencyCode;
                        if (_.has(ecommerce, 'add')) {
                            var itemsAdded = self.processData(ecommerce.add.products);

                            $("body").trigger("mpItemAddToCart", [itemsAdded, dataLayer]);
                            cartGenericLayer.add = {
                                'products': itemsAdded
                            };
                        }

                        if (_.has(ecommerce, 'remove')) {
                            var itemsRemoved = self.processData(ecommerce.remove.products);

                            $("body").trigger("mpItemRemoveFromCart", [itemsRemoved, dataLayer]);
                            cartGenericLayer.remove = {
                                'products': itemsRemoved
                            };
                        }
                    }

                    if (!_.isEmpty(cartGenericLayer)) {
                        cartItem.cart = cartGenericLayer;
                    }

                    dataLayer.push(cartItem);
                });
            }
        },
        /**
         * Store product collection
         * @param data
         */
        setProductCollection: function (data) {
            var self = this;
            if (_.isObject(data) && _.has(data,'ecommerce') && _.has(data.ecommerce, 'impressions')) {
                _.each(data.ecommerce.impressions, function (product) {
                    if (_.has(product, 'p_id')) {
                        self.productCollection[product.p_id] = product;
                    }
                });
            }
        },
        /**
         * Bind product click event
         */
        trackClick: function () {
            var self = this;

            _.each(this.config.productLists, function (list) {
                $('body').on('click', list.class_name, function () {
                    return self.productClick($(this), list);
                });
            });
        },
        /**
         * Track product click event
         */
        productClick: function ($element, list) {
            var $container = $element.closest(list.container_class);
            var $priceBox = $container.find("[data-product-id]");
            var productUrl = null;

            if ($container.find('a.product-item-link').length) {
                productUrl = $container.find('a.product-item-link').attr('href');
            } else if ($container.find('a.product-item-photo').length) {
                productUrl = $container.find('a.product-item-photo').attr('href');
            } else {
                productUrl = $element.attr('href');
            }

            if ($priceBox.length) {
                var productId = $priceBox.data('productId');
                var product = {};

                if (productId && _.has(this.productCollection, productId)) {
                    product = this.productCollection[productId];
                } else if (productId) {
                    product = this.productCollection[_.first(_.keys(this.productCollection))];
                    if (_.has(product, 'p_id')) {
                        var price = 0;

                        if ($container.find("[data-price-amount]").data('priceAmount') > 0) {
                            price = $container.find("[data-price-amount]").data('priceAmount');
                        }

                        product.p_id = productId;
                        product.id = $container.find("[data-product-sku]").data('productSku');
                        product.position = $container.index() > 0 ? $container.index() : 0;
                        product.name = $.trim($container.find(".product-item-link").text());
                        product.price = price;
                    } else {
                        product = {};
                    }
                }

                if (_.has(product, 'p_id')) {
                    //if gtm take longer than 3 seconds
                    var autoRedirectTimer = setTimeout(function () {
                        document.location = productUrl
                    }, 3000);

                    $("body").trigger("mpProductClick", [product, this.getDataLayerObject(), list]);

                    try {
                        var storage = this.getStorageData();
                        if (!_.isEmpty(storage)) {
                            storage.set('product-click', {
                                pid: productId,
                                sku: product.id,
                                list: list.list_type,
                                position: product.position
                            });
                        }

                    } catch (e) {
                    }

                    this.getDataLayerObject().push({
                        'event': 'productClick',
                        'ecommerce': {
                            'click': {
                                'actionField': {
                                    'list': list.list_type
                                },
                                'products': [product]
                            }
                        },
                        'eventCallback': function () {
                            clearTimeout(autoRedirectTimer);
                            document.location = productUrl
                        }
                    });

                    return false;
                }
            }

            return true;
        }
    });

});
