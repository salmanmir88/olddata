<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Observer;

class AddToWishlist implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Apptrian\FacebookPixel\Helper\Data
     */
    public $helper;
    
    /**
     * Constructor.
     *
     * @param \Apptrian\FacebookPixel\Helper\Data $helper
     */
    public function __construct(
        \Apptrian\FacebookPixel\Helper\Data $helper
    ) {
        $this->helper = $helper;
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
     * @param \Magento\Quote\Model\Quote $items
     * @param int $detect
     * @return array
     */
    public function getEventDataForProduct($product, $items, $detect = 0)
    {
        $productId = $product->getEntityId();
        
        $data = $this->helper->getDataForServerAddToCartOrWishlistEvent($productId, 'AddToWishlist');
        $finalData = $data['ready_data'];
        
        if ($detect) {
            $itemProductId = 0;
            $itemQtyToAdd  = 0;
            $itemPrice     = 0;
            $value         = 0;
            
            foreach ($items as $item) {
                $itemProductId = $item->getProductId();
                $itemQtyToAdd  = $item->getQty();
                
                // If there is qty to be added and item product ID
                // is the same as observer product ID
                if ($itemQtyToAdd && $itemProductId == $productId) {
                    $finalData['data'][0]['custom_data']['contents'][0]['quantity'] = $itemQtyToAdd;
                    
                    $itemPrice = $finalData['data'][0]['custom_data']['contents'][0]['item_price'];
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
     * @param \Magento\Quote\Model\Quote $items
     * @return array
     */
    public function getEventDataForBundleProduct($product, $items)
    {
        $productId        = $product->getEntityId();
        $parentQty        = 1;
        
        // Products added to the cart
        $addedProducts = [];
        
        foreach ($items as $item) {
            $bundleOptions            = $item->getBuyRequest()->getBundleOption();
            $bundleOptionQty          = $item->getBuyRequest()->getBundleOptionQty();
            $parentQty                = $item->getBuyRequest()->getQty();
            $bundleProductOptionsData = $this->helper->getBundleProductOptionsData($product);
            
            foreach ($bundleOptions as $optionId => $selection) {
                if (is_array($selection)) {
                    foreach ($selection as $selectionId) {
                        $added = $this->getBundleProductSelectedOptions(
                            $optionId,
                            $selectionId,
                            $bundleProductOptionsData,
                            $bundleOptionQty
                        );
                        
                        if (!empty($added)) {
                            $selectedProductId = $added['product_id'];
                            $addedProducts[$selectedProductId] = $added;
                        }
                    }
                } else {
                    $added = $this->getBundleProductSelectedOptions(
                        $optionId,
                        $selection,
                        $bundleProductOptionsData,
                        $bundleOptionQty
                    );
                    
                    if (!empty($added)) {
                        $selectedProductId = $added['product_id'];
                        $addedProducts[$selectedProductId] = $added;
                    }
                }
            }
        }
        
        $data = $this->helper->getDataForServerAddToCartOrWishlistEvent($productId, 'AddToWishlist');
        
        $readyData = $data['ready_data'];
        $additionalData = $data['additional_data'];
        
        $finalData = $this->getFinalData($readyData, $additionalData, $addedProducts, $parentQty);
        
        return $finalData;
    }
    
    /**
     * Returns bundle product selected options.
     *
     * @param int $optionId
     * @param int $selectionId
     * @param array $bundleProductOptionsData
     * @param array $bundleOptionQty
     * @return array
     */
    public function getBundleProductSelectedOptions(
        $optionId,
        $selectionId,
        $bundleProductOptionsData,
        $bundleOptionQty
    ) {
        $added = [];
        
        if (array_key_exists($optionId, $bundleProductOptionsData)) {
            $selectionArray = $bundleProductOptionsData[$optionId];
            
            if (array_key_exists($selectionId, $selectionArray)) {
                $selectedProductId   = $selectionArray[$selectionId]['product_id'];
                $selectedProductQty  = $selectionArray[$selectionId]['product_quantity'];
                $added['product_id'] = $selectedProductId;
                $added['qty']        = $selectedProductQty;
                
                if (array_key_exists($optionId, $bundleOptionQty)) {
                    $added['qty'] = $bundleOptionQty[$optionId];
                }
            }
        }
        
        return $added;
    }
    
    /**
     * Returns data for configurable products.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote $items
     * @return array
     */
    public function getEventDataForConfigurableProduct($product, $items)
    {
        $productId     = $product->getEntityId();
        $variant       = null;
        $variantId     = 0;
        $variantSku    = '';
        
        // Products added to the cart
        $addedProducts = [];
        
        foreach ($items as $item) {
            $variantSku = $item->getProduct()->getSku();
            $variant    = $this->helper->getProductBySku($variantSku);
            $variantId  = $variant->getEntityId();
            
            if ($productId != $variantId) {
                $itemQtyToAdd = $item->getQty();
                $addedProducts[$variantId]['product_id'] = $variantId;
                $addedProducts[$variantId]['qty']        = $itemQtyToAdd;
            }
        }
        
        $data = $this->helper->getDataForServerAddToCartOrWishlistEvent($productId, 'AddToWishlist');
        
        $readyData      = $data['ready_data'];
        $additionalData = $data['additional_data'];
        
        $finalData = $this->getFinalData($readyData, $additionalData, $addedProducts);
        
        return $finalData;
    }
    
    /**
     * Returns data for grouped products.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote $items
     * @return array
     */
    public function getEventDataForGroupedProduct($product, $items)
    {
        $productId     = $product->getEntityId();
        $itemProductId = 0;
        $group         = [];
        
        // Products added to the cart
        $addedProducts = [];
        
        foreach ($items as $item) {
            $group = $item->getBuyRequest()->getSuperGroup();
            
            foreach ($group as $itemProductId => $itemQtyToAdd) {
                // Only if qty is not zero
                if ($itemQtyToAdd) {
                    $addedProducts[$itemProductId]['product_id'] = $itemProductId;
                    $addedProducts[$itemProductId]['qty']   = $itemQtyToAdd;
                }
            }
        }
        
        $data = $this->helper->getDataForServerAddToCartOrWishlistEvent($productId, 'AddToWishlist');
        
        $readyData = $data['ready_data'];
        $additionalData = $data['additional_data'];
        
        $finalData = $this->getFinalData($readyData, $additionalData, $addedProducts);
        
        return $finalData;
    }
    
    /**
     * Execute method.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Apptrian\FacebookPixel\Observer\AddToWishlist
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $firingMode = $this->helper->getFiringMode();
        
        if ($firingMode != 2) {
            return $this;
        }
        
        if (!$this->helper->isEventEnabled('AddToWishlist', true)) {
            return $this;
        }
        
        $items = $observer->getItems();
                
        if (!$items) {
            return $this;
        }
        
        $product = null;
        
        foreach ($items as $item) {
            $product = $item->getProduct();
        }
        
        if (!$product) {
            return $this;
        }
        
        $data        = [];
        $productType = $product->getTypeId();
        
        $isDetectSelectedSkuEnabled = $this->helper
            ->isDetectSelectedSkuEnabled($productType, true);
        
        if ($isDetectSelectedSkuEnabled) {
            if ($productType == 'configurable') {
                $data = $this->getEventDataForConfigurableProduct($product, $items);
            } elseif ($productType == 'bundle') {
                $data = $this->getEventDataForBundleProduct($product, $items);
            } elseif ($productType == 'grouped') {
                $data = $this->getEventDataForGroupedProduct($product, $items);
            } else {
                $data = $this->getEventDataForProduct($product, $items, 1);
            }
        } else {
            $data = $this->getEventDataForProduct($product, $items);
        }
        
        if (empty($data)) {
            return $this;
        }
        
        $this->helper->fireServerEvent($data);
        
        return $this;
    }
}
