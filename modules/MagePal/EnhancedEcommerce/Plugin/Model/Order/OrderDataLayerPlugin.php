<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Plugin\Model\Order;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use MagePal\GoogleTagManager\DataLayer\OrderData\OrderItemProvider;
use MagePal\GoogleTagManager\DataLayer\OrderData\OrderProvider;
use MagePal\GoogleTagManager\Helper\Data as GtmHelper;
use MagePal\GoogleTagManager\Helper\DataLayerItem;
use MagePal\GoogleTagManager\Model\DataLayerEvent;
use MagePal\GoogleTagManager\Model\Order;

class OrderDataLayerPlugin
{
    /**
     * @var gtmHelper
     */
    protected $gtmHelper;

    /**
     * @var CollectionFactoryInterface
     */
    protected $_salesOrderCollection;

    /**
     * @var null
     */
    protected $_orderCollection = null;

    /**
     * @var OrderProvider
     */
    protected $orderProvider;

    /**
     * @var OrderItemProvider
     */
    protected $orderItemProvider;

    protected $dataLayerItemHelper;

    public function __construct(
        CollectionFactoryInterface $salesOrderCollection,
        GtmHelper $gtmHelper,
        OrderProvider $orderProvider,
        OrderItemProvider $orderItemProvider,
        DataLayerItem  $dataLayerItemHelper
    ) {
        $this->gtmHelper = $gtmHelper;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->orderProvider = $orderProvider;
        $this->orderItemProvider = $orderItemProvider;
        $this->dataLayerItemHelper = $dataLayerItemHelper;
    }

    /**
     * @param Order $subject
     * @param $result
     * @param $order
     */
    public function afterGetTransactionDetail(Order $subject, $result, $order)
    {
        $defaultGaFields = [
            'transactionId',
            'transactionAffiliation',
            'transactionTotal',
            'transactionSubTotal',
            'transactionShipping',
            'transactionTax',
            'transactionCouponCode',
            'transactionDiscount',
            'transactionProducts'
        ];

        foreach ($defaultGaFields as $key) {
            if (array_key_exists($key, $result)) {
                unset($result[$key]);
            }
        }

        $transaction = [
            'ecommerce' => $this->getOrderDetail($order)
        ];

        $data = array_merge_recursive($result, $transaction);
        $data['event'] = DataLayerEvent::PURCHASE_EVENT;

        return $data;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getOrderDetail($order)
    {
        $purchase = [];
        $purchase['purchase']['actionField'] = [
            'id' => $order->getIncrementId(),
            'affiliation' => $this->escapeReturn($order->getStoreName()),
            'revenue' => $this->gtmHelper->formatPrice($order->getBaseGrandTotal()),
            'tax' => $this->gtmHelper->formatPrice($order->getTaxAmount()),
            'shipping' => $this->gtmHelper->formatPrice($order->getBaseShippingAmount()),
            'coupon' => $order->getCouponCode() ? $order->getCouponCode() : ''
        ];

        $purchase['purchase']['products'] = $this->getOrderItemDetail($order);
        $purchase['currencyCode'] = $order->getStoreCurrencyCode();
        return $purchase;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getOrderItemDetail($order)
    {
        $products = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $product = [
                'id' => $item->getSku(),
                'parent_sku' => $item->getProduct() ? $item->getProduct()->getData('sku') : $item->getSku(),
                'name' => $item->getName(),
                'price' => $this->gtmHelper->formatPrice($item->getBasePrice()),
                'quantity' => $item->getQtyOrdered() * 1,
                //'brand' => ''
            ];

            if ($variant = $this->dataLayerItemHelper->getItemVariant($item)) {
                $product['variant'] = $variant;
            }

            if ($category = $this->dataLayerItemHelper->getFirstCategory($item)) {
                $product['category'] = $category;
            }

            $products[] = $this->orderItemProvider
                ->setItem($item)
                ->setItemData($product)
                ->setListType(OrderItemProvider::LIST_TYPE_GOOGLE)
                ->getData();
        }

        return $products;
    }

    /**
     * @param $data
     * @return string
     */
    public function escapeReturn($data)
    {
        return trim(str_replace(["\r\n", "\r", "\n"], ' ', $data));
    }
}
