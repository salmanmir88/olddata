define([
    'jquery'
],function ($) {
    'use strict';

    return function (Component) {
        return Component.extend({
            onRemoveClick: function (item) {
                this._super(item);
                $('body').trigger('mpCheckoutItemRemoved', [item.item_id, item.qty]);
            }
        });
    }
});
