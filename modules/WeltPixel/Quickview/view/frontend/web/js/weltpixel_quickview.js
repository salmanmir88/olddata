define([
    'jquery',
    'magnificPopup'
    ], function ($, magnificPopup) {
    "use strict";

    return {
        displayContent: function(prodUrl) {
            if (!prodUrl.length) {
                return false;
            }

            var url = window.weltpixel_quickview.baseUrl + 'weltpixel_quickview/index/updatecart';
            var showMiniCart = parseInt(window.weltpixel_quickview.showMiniCart);

            window.weltpixel_quickview.showMiniCartFlag = false;

            $.magnificPopup.open({
                items: {
                  src: prodUrl
                },
                type: 'iframe',
                closeOnBgClick: false,
                preloader: true,
                tLoading: '',
                callbacks: {
                    open: function() {
                      $('.mfp-preloader').css('display', 'block');
                      $('.mfp-content').css('height', '450px');
                      $('body').addClass("quick-view-body");
                    },
                    beforeClose: function() {
                        $('[data-block="minicart"]').trigger('contentLoading');
                        $.ajax({
                        url: url,
                        method: "POST"
                      });
                    },
                    close: function() {
                      $('.mfp-preloader').css('display', 'block');
						if($('body').hasClass("quick-view-body")){
							$('body').removeClass("quick-view-body");
						}
                    },
                    afterClose: function() {
                        /* Show only if product was added to cart and enabled from admin */
                        if (window.weltpixel_quickview.showMiniCartFlag && showMiniCart) {
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                            setTimeout(function(){
                                $('.action.showcart').trigger('click');
                            }, 1000);
                        }
                    }
                  }
            });
        }
    };

});
