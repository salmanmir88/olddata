/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

define([
    'jquery',
    'dataLayerShareComponent',
    'underscore'
], function ($, dataLayerShareComponent, _) {

    return function (config) {
        var shareComponent = dataLayerShareComponent();
        var dataLayer = shareComponent.setConfig(config).getDataLayerObject();

        $(document).on('ajax:addToCart:error', function (data) {
            dataLayer.push({
                'event': 'addToCartFailed',
                'productErrors': {
                    'product': getProductData()
                }
            });
        });

        /**
         * Add to cart failed missing options
         */
        $('#product_addtocart_form').submit(function () {
            var $form = $(this);
            if (!$form.validation('isValid')) {
                dataLayer.push({
                    'event': 'addToCartItemOptionRequired',
                    'productErrors': {
                        'product': getProductData()
                    }
                });
            }
            return true;
        });

        var getProductData = function () {
            var dl = shareComponent.loadDataLayer()
            var result = {};
            if (_.has(dl, 'product')) {
                result = {
                    'name': dl.product.name,
                    'sku': dl.product.sku,
                    'price': dl.product.price,
                    'p_id': dl.product.id
                }
            }

            return result;
        }
    }
});
