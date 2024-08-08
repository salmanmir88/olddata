<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Data;

use ArrayIterator;
use Magento\Framework\Exception\LocalizedException;
use MagePal\EnhancedEcommerce\Block\CatalogLayer;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

class Wishlist extends CatalogLayer
{
    /**
     * Add category data to datalayer
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _dataLayer()
    {
        if ($list = $this->_eeHelper->getWishListType()) {
            $this->setListType($list);
        }

        $collection = $this->_getProductCollection();

        if (is_object($collection) && $collection->count()) {
            $itemCollection = [];

            //convert wishlist collection
            foreach ($collection as $item) {
                if ($item->getProduct()) {
                    $itemCollection[] = $item->getProduct();
                }
            }

            $wishlistCollection = new ArrayIterator($itemCollection);

            $products = $this->getProductImpressions($wishlistCollection);

            $this->setImpressionList(
                $this->getListType(),
                $this->_eeHelper->getWishListClassName(),
                $this->_eeHelper->getWishListContainerClass()
            );

            $impressionsData = [
                'event' => DataLayerEvent::PRODUCT_IMPRESSION_EVENT,
                'ecommerce' => [
                    'impressions' => $products,
                    'currencyCode' => $this->getStoreCurrencyCode()
                ]
            ];

            $this->addCustomDataLayerByEvent(DataLayerEvent::PRODUCT_IMPRESSION_EVENT, $impressionsData);
        }

        return $this;
    }

    protected function isRelatedOrCrosssell()
    {
        return true;
    }
}
