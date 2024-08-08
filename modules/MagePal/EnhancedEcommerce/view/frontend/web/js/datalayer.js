/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

define([
    'underscore',
    'jquery',
    'dataLayerShareComponent'
], function (_, $, dataLayerShareComponent) {
    'use strict';

    return function (config) {
        var shareComponent = dataLayerShareComponent();
        shareComponent.setConfig(config);

        if (_.isArray(config.data)) {
            var storedData = null;

            _.each(config.data, function (data) {
                if (_.has(data, 'event') && data.event === 'productDetail') {
                    if (storedData === null) {
                        storedData = shareComponent.getProductClickStorageData();
                    }

                    if (_.has(storedData, 'sku') && _.has(storedData, 'list')) {
                        if (_.contains(_.pluck(data.ecommerce.detail.products, "id"), storedData.sku)) {
                            data.ecommerce.detail.actionField = {list: storedData.list};
                        }
                    }

                    if (_.has(storedData, 'position')) {
                        _.each(data.ecommerce.detail.products, function (data) {
                            data.position = storedData.position;
                        });
                    }
                }
                shareComponent.setProductCollection(data);
                shareComponent.getDataLayerObject().push(data);
            });
        }

        shareComponent.trackClick();
    }
});
