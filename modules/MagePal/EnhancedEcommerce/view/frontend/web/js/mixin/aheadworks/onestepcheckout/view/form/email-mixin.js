define([
    'jquery'
],function ($) {
    'use strict';
    var trigger = false;

    return function (Component) {
        return Component.extend({
            checkEmailAvailability: function () {
                this._super();

                this.isPasswordVisible.subscribe(function (emailAvailable) {
                    trigger = true;
                    $('body').trigger('mpCheckoutEmailValidation', emailAvailable);
                });

                this.isLoading.subscribe(function (isLoadingState) {
                    if (!trigger && !isLoadingState) {
                        trigger = true;
                        $('body').trigger('mpCheckoutEmailValidation', false);
                    }
                });
            }
        });
    }
});
