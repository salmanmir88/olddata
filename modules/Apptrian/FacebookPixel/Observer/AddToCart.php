<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Observer;

class AddToCart implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Apptrian\FacebookPixel\Helper\Data
     */
    public $helper;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * Constructor.
     *
     * @param \Apptrian\FacebookPixel\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Apptrian\FacebookPixel\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->helper          = $helper;
        $this->checkoutSession = $checkoutSession;
    }
    
    /**
     * Returns final data for the event.
     *
     * @param array $data
     * @param array $additionalData
     * @param array $addedData
     * @param int $parentQty
     * @return array
     */
    public function getFinalData($data, $additionalData, $addedData, $parentQty = 1)
    {
        if (empty($data)) {
            return [];
        }
        
        if (empty($additionalData) || empty($addedData)) {
            return $data;
        }
        
        $contents = [];
        $content  = [];
        
        $itemPrice = 0;
        $itemQty   = 0;
        $value     = 0;
        
        foreach ($addedData as $id => $info) {
            $content  = [];
            if (array_key_exists($id, $additionalData)) {
                $content = $additionalData[$id];
                $itemQty   = $info['qty'];
                $itemQty  *= $parentQty;
                
                if (array_key_exists('price', $info)) {
                    $itemPrice = $info['price'];
                    if ($itemPrice) {
                        // Set detected price
                        $content['item_price'] = $itemPrice;
                    }
                }
                
                // Set detected qty
                $content['quantity'] = $itemQty;
                
                // Add detected product content
                $contents[] = $content;
            }
        }
        
        if (!empty($contents)) {
            $data['data'][0]['custom_data']['contents'] = $contents;
            
            // Go through contencts and get the value
            // Must be done like this because of various ways
            // this method can be called
            foreach ($contents as $content) {
                $itemPrice = $content['item_price'];
                $itemQty   = $content['quantity'];
                $value += $itemPrice * $itemQty;
            }
        }
        
        if ($value) {
            $data['data'][0]['custom_data']['value'] = $this->helper->formatPrice($value);
        }
        
        // Make sure content_type is product (it cannot be product_group)
        $data['data'][0]['custom_data']['content_type'] = 'product';
        
        return $data;
    }
    
    /**
     * Returns data for downloadable, simple, virtual, etc. products.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $detect
     * @return array
     */
    public function getEventDataForProduct($product, $quote, $detect = 0)
    {
        $productId = $product->getEntityId();
        
        $data = $this->helper->getDataForServerAddToCartOrWishlistEvent($productId);
        $finalData = $data['ready_data'];
        
        if ($detect) {
            $taxFlag       = $this->helper->getDisplayTaxFlag();
            $items         = $quote->getAllVisibleItems();
            $itemProductId = 0;
            $itemQtyToAdd  = 0;
            $itemPrice     = 0;
            $value         = 0;
            
            foreach ($items as $item) {
                $itemProductId = $item->getProductId();
                $itemQtyToAdd  = $item->getQtyToAdd();
                
                // If there is qty to be added and item product ID
                // is the same as the observer product ID
                if ($itemQtyToAdd && $itemProductId == $productId) {
                    $finalData['data'][0]['custom_data']['contents'][0]['quantity'] = $itemQtyToAdd;
                    
                    if ($taxFlag) {
                        $itemPrice = $this->helper->formatPrice($item->getPriceInclTax());
                    } else {
                        $itemPrice = $this->helper->formatPrice($item->getPrice());
                    }
                    
                    $finalData['data'][0]['custom_data']['contents'][0]['item_price'] = $itemPrice;
                    $value = $this->helper->formatPrice($itemPrice * $itemQtyToAdd);
                    $finalData['data'][0]['custom_data']['value'] = $value;
                    
                    // No need to go on product is found and data is updated
                    break;
                }
            }
        }
        
        return $finalData;
    }
    
    /**
     * Returns data for bundle products.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getEventDataForBundleProduct($product, $quote)
    {
        $taxFlag          = $this->helper->getDisplayTaxFlag();
        $items            = $quote->getAllVisibleItems();
        $allItems         = $quote->getAllItems();
        $productId        = $product->getEntityId();
        $itemProductId    = 0;
        $itemQtyToAdd     = 0;
        $options          = [];
        $variantIds       = [];
        $parentQty        = 1;
        
        // Products added to the cart
        $addedProducts = [];
        
        // Get all the child product Ids and paret product qty
        foreach ($items as $item) {
            $itemProductId = $item->getProductId();
            $itemQtyToAdd  = $item->getQtyToAdd();
            
            // If there is qty to be added and item product ID
            // is the same as observer product ID
            if ($itemQtyToAdd && $itemProductId == $productId) {
                $options    = $item->getQtyOptions();
                $variantIds = array_keys($options);
                
                $parentQty = (int) $itemQtyToAdd;
            }
        }
        
        // Go through all child products
        foreach ($allItems as $item) {
            $itemProductId = $item->getProductId();
            $itemQtyToAdd  = $item->getQtyToAdd();
            
            // If there is qty to add and
            // item product ID is the array of child products
            if ($itemQtyToAdd && in_array($itemProductId, $variantIds)) {
                $addedProducts[$itemProductId]['product_id'] = $itemProductId;
                $addedProducts[$itemProductId]['qty']   = $itemQtyToAdd;
                
                if ($taxFlag) {
                    $addedProducts[$itemProductId]['price'] = $this->helper->formatPrice($item->getPriceInclTax());
                } else {
                    $addedProducts[$itemProductId]['price'] = $this->helper->formatPrice($item->getPrice());
                }
            }
        }
        
        $data = $this->helper->getDataForServerAddToCartOrWishlistEvent($productId);
        
        $readyData = $data['ready_data'];
        $additionalData = $data['additional_data'];
        
        $finalData = $this->getFinalData($readyData, $additionalData, $addedProducts, $parentQty);
        
        return $finalData;
    }
    
    /**
     * Returns data for configurable products.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getEventDataForConfigurableProduct($product, $quote)
    {
        $taxFlag       = $this->helper->getDisplayTaxFlag();
        $items         = $quote->getAllVisibleItems();
        $productId     = $product->getEntityId();
        $variantSku    = $product->getSku();
        $itemProductId = 0;
        $options       = [];
        $variantIds    = [];
        $variantId     = 0;
        
        // Products added to the cart
        $addedProducts = [];
        
        foreach ($items as $item) {
            $itemSku      = $item->getSku();
            $itemQtyToAdd = $item->getQtyToAdd();
            
            // If there is qty to add and and item SKU
            // is the same as product SKU from the observer
            if ($itemQtyToAdd && $itemSku == $variantSku) {
                $options       = $item->getQtyOptions();
                $variantIds    = array_keys($options);
                $variantId     = $variantIds[0];
                $itemProductId = $variantId;
                
                $addedProducts[$itemProductId]['product_id'] = $itemProductId;
                $addedProducts[$itemProductId]['qty']        = $itemQtyToAdd;
                
                if ($taxFlag) {
                    $addedProducts[$itemProductId]['price'] = $this->helper->formatPrice($item->getPriceInclTax());
                } else {
                    $addedProducts[$itemProductId]['price'] = $this->helper->formatPrice($item->getPrice());
                }
            }
        }
        
        $data = $this->helper->getDataForServerAddToCartOrWishlistEvent($productId);
        
        $readyData      = $data['ready_data'];
        $additionalData = $data['additional_data'];
        
        $finalData = $this->getFinalData($readyData, $additionalData, $addedProducts);
        
        return $finalData;
    }
    
    /**
     * Returns data for grouped products.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getEventDataForGroupedProduct($product, $quote)
    {
        $taxFlag       = $this->helper->getDisplayTaxFlag();
        $items         = $quote->getAllVisibleItems();
        $productId     = $product->getEntityId();
        $itemProductId = 0;
        $parentId      = 0;
        
        // Products added to the cart
        $addedProducts = [];
        
        foreach ($items as $item) {
            $itemProductId = $item->getProductId();
            $itemQtyToAdd  = $item->getQtyToAdd();
            
            // If there is qty to add and
            // item product type is grouped
            if ($itemQtyToAdd && $item->getProductType() == 'grouped') {
                $parentId = $this->helper->getParentGroupedProductId($itemProductId);
                
                // If parent ID is the same as product ID from the observer
                if ($parentId == $productId) {
                    $addedProducts[$itemProductId]['product_id'] = $itemProductId;
                    $addedProducts[$itemProductId]['qty']   = $itemQtyToAdd;
                    
                    if ($taxFlag) {
                        $addedProducts[$itemProductId]['price'] = $this->helper->formatPrice($item->getPriceInclTax());
                    } else {
                        $addedProducts[$itemProductId]['price'] = $this->helper->formatPrice($item->getPrice());
                    }
                }
            }
        }
        
        $data = $this->helper->getDataForServerAddToCartOrWishlistEvent($productId);
        
        $readyData = $data['ready_data'];
        $additionalData = $data['additional_data'];
        
        $finalData = $this->getFinalData($readyData, $additionalData, $addedProducts);
        
        return $finalData;
    }
    
    /**
     * Execute method.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Apptrian\FacebookPixel\Observer\AddToCart
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $firingMode = $this->helper->getFiringMode();
        
        if ($firingMode != 2) {
            return $this;
        }
        
        if (!$this->helper->isEventEnabled('AddToCart', true)) {
            return $this;
        }
        
        $product = $observer->getProduct();
        
        if (!$product) {
            return $this;
        }
        
        $quote = $this->checkoutSession->getQuote();
                
        if (!$quote) {
            return $this;
        }
        
        $data        = [];
        $productType = $product->getTypeId();
        
        $isDetectSelectedSkuEnabled = $this->helper
            ->isDetectSelectedSkuEnabled($productType, true);
        
        if ($isDetectSelectedSkuEnabled) {
            if ($productType == 'configurable') {
                $data = $this->getEventDataForConfigurableProduct($product, $quote);
            } elseif ($productType == 'bundle') {
                $data = $this->getEventDataForBundleProduct($product, $quote);
            } elseif ($productType == 'grouped') {
                $data = $this->getEventDataForGroupedProduct($product, $quote);
            } else {
                $data = $this->getEventDataForProduct($product, $quote, 1);
            }
        } else {
            $data = $this->getEventDataForProduct($product, $quote);
        }
        
        if (empty($data)) {
            return $this;
        }
        
        $this->helper->fireServerEvent($data);
        
        return $this;
    }
}
