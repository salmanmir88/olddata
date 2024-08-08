<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Model;

use Magento\Framework\Session\SessionManager;
use MagePal\GoogleTagManager\Model\DataLayerEvent;

/**
 * @method setData($name, $value)
 */
class Session extends SessionManager
{
    /**
     * @param $items
     * @return Session
     */
    public function setItemAddToCart($items)
    {
        $data = $this->getData('updated_qty_items');

        if (!empty($items) && is_array($data)) {
            $items = array_merge($data, $items);
        }

        $this->setData('updated_qty_items', $items);

        return $this;
    }

    /**
     * @param bool $clear
     * @return mixed
     */
    public function getItemAddToCart($clear = false)
    {
        return $this->getData('updated_qty_items', $clear);
    }

    /**
     * @param $items
     * @return $this
     */
    public function setItemRemovedFromCart(array $items)
    {
        $data = $this->getData('deleted_qty_items');

        if (!empty($items) && is_array($data)) {
            $items = array_merge($data, $items);
        }

        $this->setData('deleted_qty_items', $items);

        return $this;
    }

    /**
     * @param bool $clear
     * @return mixed
     */
    public function getItemRemovedFromCart($clear = false)
    {
        return $this->getData('deleted_qty_items', $clear);
    }

    /**
     * @param $item
     * @param $event
     * @return Session
     */
    public function setGenericEvent($item, $event)
    {
        $data = $this->getData('generic_items');
        $items = [];

        if (!empty($item)) {
            $items[] = [
                'event' => $event,
                'productErrors' => [
                    'product' => $item
                ]
            ];

            if (!empty($items) && is_array($data)) {
                $items = array_merge($data, $items);
            }

            $this->setData('generic_items', $items);
        }

        return $this;
    }

    /**
     * @param bool $clear
     * @return mixed
     */
    public function getGenericEvent($clear = false)
    {
        return (array) $this->getData('generic_items', $clear);
    }

    /**
     * @return array
     */
    public function getProductDataObjectArray()
    {
        $itemAdded = $this->getItemAddToCart(true);
        $itemRemoved = $this->getItemRemovedFromCart(true);
        $genericEvents = $this->getGenericEvent(true);

        $result = [];

        if (!empty($itemAdded) && is_array($itemAdded)) {
            $result[] =  [
                'event' => DataLayerEvent::ADD_TO_CART_EVENT,
                'ecommerce' => [
                    'add' => [
                        'products' => $itemAdded
                    ]
                ]
            ];
        }

        if (!empty($itemRemoved) && is_array($itemRemoved)) {
            $result[] =  [
                'event' => DataLayerEvent::REMOVE_FROM_CART_EVENT,
                'ecommerce' => [
                    'remove' => [
                        'products' => $itemRemoved
                    ]
                ]
            ];
        }

        if (!empty($genericEvents)) {
            foreach ($genericEvents as $event) {
                $result[] = $event;
            }
        }

        return $result;
    }
}
