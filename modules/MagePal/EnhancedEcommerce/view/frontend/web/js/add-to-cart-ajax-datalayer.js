/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

define([
    'Magento_Customer/js/customer-data',
    'dataLayerShareComponent'
], function (customerData, dataLayerShareComponent) {
    'use strict';

    return function (options) {
        customerData.get("magepal-eegtm-jsdatalayer").subscribe(function (data) {
            var shareComponent = dataLayerShareComponent();
            shareComponent.setConfig(options).pushAddRemoveItemDataLayer(data);
        }, this);
    }
});
