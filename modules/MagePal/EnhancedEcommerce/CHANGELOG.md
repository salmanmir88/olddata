1.7.1
=============
* ##### New Features:
    * none

* ##### Fixed bugs:
    * Fix issue with <a href="https://amasty.com/ajax-shopping-cart-for-magento-2.html?a=magepal" rel="nofollow" _blank="new">Amasty Ajax to Cart</a>
    * Fix GA4 add to cart issue when use in combine with our Google Enhanced Ecommerce 
    
1.7.0
=============
* ##### New Features:
    * Add support for Enhanced Ecommerce & GA4 to work side by side

* ##### Fixed bugs:
    * none


1.6.1
=============
* ##### New Features:
    * Add Related, Upsell, cross Sell to Admin config

* ##### Fixed bugs:
    * Fix issue with Product Detail list element
    * Fix issue with Product Detail position element
    
1.6.0
=============
* ##### New Features:
    * None

* ##### Fixed bugs:
    * Code refactoring
    
1.5.3
=============
* ##### New Features:
  * None

* ##### Fixed bugs:
  * Fix issue capturing the correct product category and position on product detail page
  * Fix add/update/remove product qty for Aheadworks OneStepCheckout mini checkout side cart

1.5.1
=============
* ##### New Features:
  * Add support for Magento 2.4.0 
  * Add support for php 7.4
  * Add support (partial) for Aheadworks OneStepCheckout

* ##### Fixed bugs:
  * Fix wrong url generated for add cart tracking url tracking

1.5.0
=============
* ##### New Features:
  * Fix issue with add to cart with PayPal on product detail page in Magento 2.3.5
  * Add new add to cart events (Missing options and item out of stock events)
  * Add new option for category product impression list type
  * Add currency code to product data push event to better support currency conversion
  * Add Wishlist product impression
  * Add Compare product impression
  * Add new admin option for category product impression list name
  * Add limited support for <a href="https://amasty.com/one-step-checkout-for-magento-2.html?a=magepal" rel="nofollow" _blank="new">Amasty One Step Checkout</a>

* ##### Fixed bugs:
  * paymentMethodAdded not triggering on some payment method

1.4.1
=============
* ##### New Features:
  * Add PHP 7.3 support

1.4.0
=============
* ##### New Features:
  * Add data layer API for products and categories object
  * Code refactoring
  * Add position to data layer
  * Other minor improvements

1.2.1
=============
* ##### New Features:
  * Add support for <a href="https://amasty.com/ajax-shopping-cart-for-magento-2.html?a=magepal" rel="nofollow" _blank="new">Amasty Ajax to Cart</a>
  * Add checkout events
    * checkoutEmailValidation
    * shippingMethodAdded
    * checkoutShippingStepCompleted
    * checkoutShippingStepFailed
    * paymentMethodAdded
    * checkoutPaymentStepCompleted
    * checkoutPaymentStepFailed
  * Add Javascript Trigger
    * mpCheckoutShippingStepValidation
    * mpCheckoutPaymentStepValidation

* ##### Fixed bugs:
  * Fix "class does not exist" when saving admin order
  * Update checkout steps logic to prevent duplicate events


1.2.0
=============
* ##### New Features:
  * Add product variant
  * Add product category (auto select first category)
  * Api to quickly add more content to the data layer (see base GTM for more information)
  * Add jQuery trigger event for mpCustomerSession, mpItemAddToCart, mpItemRemoveFromCart, mpCheckout and mpCheckoutOption

* ##### Fixed bugs:
  * Add 'currencyCode' to every page
  * Edit add to cart item on product detail page


1.1.6
=============
* ##### New Features:
  * Add support for Enhanced Success Page

* ##### Fixed bugs:
  * None


1.1.5
=============
* ##### New Features:
  * Add support for canceled order refund tracking
  * Add support for admin order tracking

* ##### Fixed bugs:
  * Fixed issue with admin Credit Memo Refund not reporting correctly


1.1.4
=============
* ##### New Features:
  * None

* ##### Fixed bugs:
  * Fixed file corruption in system.xml


1.1.3
=============
* ##### New Features:
  * None

* ##### Fixed bugs:
  * Fixed typo in system.xml
  * Add currency code to product data layer


1.1.2
=============
* ##### New Features:
  * Add support for 2.3.0

* ##### Fixed bugs:
  * None
  
  
1.1.1
=============  
* First release
