define([
    'jquery'
],function ($) {
    'use strict';

    return function (Component) {
        return Component.extend({
            updateQtyValue: function (item) {
                this._super(item);
                var qty = item.qty;

                if (this._isManuallyUpdateAllowed(qty)) {
                    $('body').trigger('mpCheckoutItemQtyChanged', [item.item_id, qty, this.qtyOriginal]);
                }
            },
            onIncrementQtyClick: function (item) {
                var qtyOriginal = item.qty;
                this._super(item);
                var qty = item.qty

                if (qty !== qtyOriginal) {
                    $('body').trigger('mpCheckoutItemQtyChanged', [item.item_id, qty, qtyOriginal]);
                }
            },
            onDecrementQtyClick: function (item) {
                var qtyOriginal = item.qty;
                this._super(item);
                var qty = item.qty

                if (qty !== qtyOriginal) {
                    $('body').trigger('mpCheckoutItemQtyChanged', [item.item_id, qty, qtyOriginal]);
                }
            },
        });
    }
});
