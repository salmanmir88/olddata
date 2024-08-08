<?php
/**
 * Webkul Odoomagentoconnect Order ResourceModel
 *
 * @author    Webkul
 * @api
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Order ResourceModel Class
 */
class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Backend\Model\Session $session,
        \Magento\Catalog\Model\Product $catalogModel,
        \Magento\Customer\Model\Customer $customerObj,
        \Magento\Sales\Model\Order\Item $orderItemModel,
        \Magento\Sales\Model\ResourceModel\Order\Tax\Item $taxItemModel,
        \Magento\Tax\Model\Calculation\Rate $taxRateModel,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Customer $customerModel,
        \Webkul\Odoomagentoconnect\Model\Customer $customerMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Customer $customerMapResource,
        \Webkul\Odoomagentoconnect\Model\Product $productMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product $productMapResource,
        \Webkul\Odoomagentoconnect\Model\Tax $taxMapping,
        
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Tax $taxMapResource,
        \Webkul\Odoomagentoconnect\Model\Payment $paymentMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Payment $paymentMapResource,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Currency $currencyMapResource,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Carrier $carrierMapResource,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Template $templateMapResource,
        $resourcePrefix = null
    ) {
        $this->_eventManager = $eventManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_session = $session;
        $this->_catalogModel = $catalogModel;
        $this->_customerObj = $customerObj;
        $this->_orderItemModel = $orderItemModel;
        $this->_taxItemModel = $taxItemModel;
        $this->_taxRateModel = $taxRateModel;
        $this->_configModel = $configModel;
        $this->_connection = $connection;
        $this->_customerModel = $customerModel;
        $this->_customerMapping = $customerMapping;
        $this->_customerMapResource = $customerMapResource;
        $this->_productMapping = $productMapping;
        $this->_productMapResource = $productMapResource;
        $this->_taxMapping = $taxMapping;
        $this->_taxMapResource = $taxMapResource;
        $this->_paymentMapping = $paymentMapping;
        $this->_paymentMapResource = $paymentMapResource;
        $this->_currencyMapResource = $currencyMapResource;
        $this->_carrierMapResource = $carrierMapResource;
        $this->_templateMapResource = $templateMapResource;
        parent::__construct($context, $resourcePrefix);
    }

    public function exportOrder($thisOrder, $quote=false)
    {
        $odooId = 0;
        $helper = $this->_connection;
        $mageOrderId = $thisOrder->getId();
        $incrementId = $thisOrder->getIncrementId();
        $currencyCode = $thisOrder->getOrderCurrencyCode();
        $pricelistId = $this->_currencyMapResource
            ->syncCurrency($currencyCode);
        if (!$pricelistId) {
            $error = "Export Error, Order ".$incrementId
                ." >> No Pricelist found for currency ".$currencyCode." at odoo end.";
            $helper->addError($error);
            return 0;
        }
        $odooAddressArray = $this->getOdooOrderAddresses($thisOrder);

        if (count(array_filter($odooAddressArray)) == 3) {
            $lineids = '';
            $partnerId = $odooAddressArray[0];
            $odooOrder = $this->createOdooOrder($thisOrder, $pricelistId, $odooAddressArray);
            if (!$odooOrder) {
                return $odooId;
            }
            $odooId = (int)$odooOrder[0];
            $orderName = $odooOrder[1];
            if ($odooId) {
                $lineids = $this->createOdooOrderLine($thisOrder, $odooId);
                $includesTax = $helper->getStoreConfig('tax/calculation/price_includes_tax');
                $this->_eventManager
                    ->dispatch(
                        'odoo_order_sync_after',
                        ['mage_order_id' => $mageOrderId, 'odoo_order_id' => $odooId]
                    );
                if ($thisOrder->getShippingDescription()) {
                    $shippingLineId = $this->createOdooOrderShippingLine($thisOrder, $odooId);
                    $lineids .= $shippingLineId;
                }
                /* Creating Order Mapping At both End..*/
                $this->createOrderMapping($thisOrder, $odooId, $orderName, $partnerId, $lineids);

                $draftState = $helper->getStoreConfig('odoomagentoconnect/order_settings/draft_order');
                $autoInvoice = $helper->getStoreConfig('odoomagentoconnect/order_settings/invoice_order');
                $autoShipment = $helper->getStoreConfig('odoomagentoconnect/order_settings/ship_order');
                if (!$draftState) {
                    $this->confirmOdooOrder($odooId);
                }
                if ($thisOrder->hasInvoices() && $autoInvoice==1) {
                    $this->invoiceOdooOrder($thisOrder, $odooId, false);
                }

                if ($thisOrder->hasShipments() && $autoShipment == 1) {
                    $this->deliverOdooOrder($thisOrder, $odooId);
                }
                return $odooId;
            } else {
                return $odooId;
            }
        } else {
            return $odooId;
        }
    }

    public function createOdooOrder($thisOrder, $pricelistId, $odooAddressArray)
    {
        $odooOrder = [];
        $extraFieldArray = [];
        $odooOrderId = 0;
        $this->_session->setExtraFieldArray($extraFieldArray);
        $this->_eventManager->dispatch('odoo_order_sync_before', ['mage_order_id' => $thisOrder->getId()]);

        $helper = $this->_connection;
        $helper->getSocketConnect();
        $extraFieldArray = $this->_session->getExtraFieldArray();
        $incrementId = $thisOrder->getIncrementId();
        $orderArray =  [
                    'partner_id'=>$odooAddressArray[0],
                    'partner_invoice_id'=>$odooAddressArray[1],
                    'partner_shipping_id'=>$odooAddressArray[2],
                    'pricelist_id'=>$pricelistId,
                    'date_order'=>$thisOrder->getCreatedAt(),
                    'origin'=>$incrementId,
                    'warehouse_id'=>$this->_session->getOdooWarehouse(),
                    'ecommerce_channel'=>'magento2',
                    'ecommerce_order_id'=>$thisOrder->getId(),
                ];
        $allowSequence = $helper->getStoreConfig('odoomagentoconnect/order_settings/order_name');
        if ($allowSequence) {
            $orderArray['name'] = $incrementId;
        }
        /* Adding Shipping Information*/
        if ($thisOrder->getShippingMethod()) {
            $shippingMethod = $thisOrder->getShippingMethod();
            $shippingCode = explode('_', $shippingMethod);
            if ($shippingCode) {
                $shippingCode = $shippingCode[0];
                $odooCarrierData =  $this->_carrierMapResource
                ->checkSpecificCarrier($shippingCode);
                $odooCarrierId = $odooCarrierData[0];
                if ($odooCarrierId > 0) {
                    $orderArray['carrier_id'] = (int)$odooCarrierId;
                }
            }
        }
        /* Adding Payment Information*/
        $paymentMethod = $thisOrder->getPayment()->getMethodInstance()->getTitle();
        if ($paymentMethod) {
            $paymentInfo = 'Payment Information:- '.$paymentMethod;
            $orderArray['note'] = $paymentInfo;
        }
        /* Adding Extra Fields*/
        foreach ($extraFieldArray as $field => $value) {
            $orderArray[$field]= $value;
        }
        $resp = $helper->callOdooMethod(
            'wk.skeleton',
            'create_order',
            [$orderArray],
            true
        );
        if ($resp && $resp[0]) {
            $odooOrder = [$resp[1]['order_id'], $resp[1]['order_name']];
        } else {
            $respMessage = $resp[1]['status_message'];
            $error = "Export Error, Order #".$incrementId." >> ".$respMessage;
            $helper->addError($error);
        }
        return $odooOrder;
    }

    /**
     * Get Tax Items with order tax information
     *
     * @param int $orderId
     * @return array
     */
    public function getTaxItemsByOrderId($orderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['item' => $this->getTable('sales_order_tax')],
            ['code', 'title', 'order_id']
        )->where(
            'item.order_id = ?',
            $orderId
        );

        return $connection->fetchAll($select);
    }

    /**
     * Get Tax Items with order item tax information
     *
     * @param int $orderItemId
     * @return array
     */
    public function getTaxItemsByOrderItemId($orderItemId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['item' => $this->getTable('sales_order_tax_item')],
            [
                'tax_id',
                'tax_percent',
                'item_id',
                'taxable_item_type',
                'associated_item_id',
                'real_amount',
                'real_base_amount',
            ]
        )->join(
            ['tax' => $this->getTable('sales_order_tax')],
            'item.tax_id = tax.tax_id',
            ['code', 'title', 'order_id']
        )->where(
            'item.item_id = ?',
            $orderItemId
        );

        return $connection->fetchAll($select);
    }

    public function createOdooOrderLine($thisOrder, $odooId, $thisQuote=false)
    {
        $odooProductId = 0;
        $lineIds = '';
        $items = $thisOrder->getAllItems();
        if (!$items) {
            return false;
        }
        /* Odoo Conncetion Data*/
        $helper = $this->_connection;
        $mageOrderId = $thisOrder->getId();
        $incrementId = $thisOrder->getIncrementId();
        $shippingIncludesTax = $helper->getStoreConfig('tax/calculation/shipping_includes_tax');
        $priceIncludesTax = $helper->getStoreConfig('tax/calculation/price_includes_tax');

        foreach ($items as $item) {
            $itemId = $item->getId();
            $itemDesc = $item->getName();
            $productId = $item->getProductId();
            $product = $this->_catalogModel->load($productId);
            if ($priceIncludesTax) {
                $basePrice = $item->getPriceInclTax();
            } else {
                $basePrice = $item->getPrice();
            }
            $itemTaxPercent = $item->getTaxPercent();
            $itemType = $item->getProductType();
            if ($itemType == 'configurable') {
                continue;
            }
            if ($itemType == 'bundle') {
                $priceType = $product->getPriceType();
                if (!$priceType) {
                    $basePrice = 0;
                }
            }
            $discountAmount = 0;
            $discountAmount = $item->getDiscountAmount();
            $quoteItemId = $item->getQuoteItemId();
            if ($item->getParentItemId() != null) {
                $parentId = $item->getParentItemId();
                $parent = $this->_orderItemModel->load($parentId);
                if ($parent->getProductType() == 'configurable') {
                    if ($priceIncludesTax) {
                        $basePrice = $parent->getPriceInclTax();
                    } else {
                        $basePrice = $parent->getPrice();
                    }
                    $itemTaxPercent = $parent->getTaxPercent();

                    $discountAmount = $parent->getDiscountAmount();
                    $itemId = $parentId;
                    $quoteItemId = $parent->getQuoteItemId();
                }

            }
            /*
                Fetching Odoo Product Id
            */
            $orderedQty = $item->getQtyOrdered();
            $mappingcollection = $this->_productMapping
                ->getCollection()
                ->addFieldToFilter('magento_id', ['eq'=>$productId]);
            if ($mappingcollection->getSize() > 0) {
                foreach ($mappingcollection as $map) {
                    $odooProductId = $map->getOdooId();
                }
            } else {
                $odooProductId = $this->syncProduct($productId);
            }
            if (!$odooProductId) {
                $error = "Odoo Product Not Found For Order ".$incrementId." Product id = ".$productId;
                $helper->addError($error);
                continue;
            }
            $orderLineArray =  [
                'order_id'=>$odooId,
                'product_id'=>(int)$odooProductId,
                'price_unit'=>$basePrice,
                'product_uom_qty'=>$orderedQty,
                'name'=>urlencode($itemDesc)
            ];
        /**************** checking tax applicable & getting mage tax id per item ************/
            if ($itemTaxPercent > 0) {
                $itemTaxes = [];
                if ($thisQuote) {
                    $qItems = $thisQuote->getAllItems();
                    foreach ($qItems as $qItem) {
                        $qItemId = $qItem->getItemId();
                        $appliedTaxes = $qItem['applied_taxes'];
                        if ($qItemId == $quoteItemId && $appliedTaxes) {
                            foreach ($appliedTaxes as $appliedTaxe) {
                                $taxCode = $appliedTaxe['id'];
                                $erpTaxId = $this->getOdooTaxId($taxCode);
                                if ($erpTaxId) {
                                    array_push($itemTaxes, $erpTaxId);
                                }
                            }
                            break;
                        }
                    }
                } else {
                    $taxItems = $this->getTaxItemsByOrderItemId($itemId); // Check taxes applied on order item
                    if (!$taxItems) {
                        $taxItems = $this->getTaxItemsByOrderId($mageOrderId); // Check taxes applied on order
                    }
                    if ($taxItems) {
                        foreach ($taxItems as $taxItem) {
                            $erpTaxId = $this->getOdooTaxId($taxItem['code']);
                            if ($erpTaxId) {
                                array_push($itemTaxes, $erpTaxId);
                            }
                        }
                    }
                }
                $orderLineArray['tax_id'] = $itemTaxes;
            } else {
                $countryId = ($thisOrder->getShippingAddress()) ? 
                    $thisOrder->getShippingAddress()->getCountryId() :
                    $thisOrder->getBillingAddress()->getCountryId();
                $itemTaxes = [];
                $taxRateData = $this->_taxRateModel
                    ->getCollection()
                    ->addFieldToFilter('rate', 0)
                    ->addFieldToFilter('tax_country_id', $countryId)
                    ->getData();
                if (count($taxRateData)) {
                    foreach ($taxRateData as $map) {
                        $taxMapData = $this->_taxMapping
                                            ->load($map['tax_calculation_rate_id'], "magento_id")
                                            ->getData();
                        if (count($taxMapData)) {
                            $erpTaxId = $taxMapData['odoo_id'];
                            if ($erpTaxId) {
                                array_push($itemTaxes, $erpTaxId);
                            }
                            $orderLineArray['tax_id'] = $itemTaxes;
                            break;
                        }
                    }
                }
            }

            $extraFieldArray = [];
            $this->_session->setExtraFieldArray($extraFieldArray);
            $this->_eventManager->dispatch('odoo_orderline_sync_before', ['item' => $item]);
            $extraFieldArray = $this->_session->getExtraFieldArray();
            foreach ($extraFieldArray as $field => $value) {
                $orderArray[$field]= $value;
            }
            $resp = $helper->callOdooMethod(
                'wk.skeleton',
                'create_sale_order_line',
                [$orderLineArray],
                true
            );
            if ($resp && $resp[0]) {
                $status = $resp[1]['status'];
                if(!$status){
                    $status_message = $resp[1]['status_message'];
                    $error = "Item Sync Error, Order ".$incrementId.", Product id = ".$productId.'Error:-'.$status_message;
                    $helper->addError($error);
                    continue;
                } else {
                    $lineId = $resp[1]['order_line_id'];
                    $lineIds .= $lineId.",";
                    if ($discountAmount != 0) {
                        $taxes = '';
                        if (isset($orderLineArray['tax_id'])) {
                            $taxes = $orderLineArray['tax_id'];
                        }
                        $productName = $product->getName();
                        $voucherLineId = $this->createOdooOrderLineVoucherLine(
                            $thisOrder,
                            $discountAmount,
                            $odooId,
                            $taxes,
                            $productName
                        );
                        $lineIds .= $voucherLineId;
                    }
                }
            } else {
                $respMessage = $resp[1]['status_message'];
                $error = "Item Sync Error, Order ".$incrementId.", Product id = ".$productId.'Error:-'.$respMessage;
                $helper->addError($error);
                continue;
            }
        }
        return $lineIds;
    }
    
    public function syncProduct($productId)
    {
        $odooProductId = 0;
        $parentIds = $this->_configModel
            ->getParentIdsByChild($productId);
        if ($parentIds) {
            $configurableId = $parentIds[0];

            $response = $this->_templateMapResource
                ->exportSpecificConfigurable($configurableId);
            if ($response['odoo_id'] > 0) {
                $odooTemplateId = $response['odoo_id'];
                $this->_templateMapResource
                    ->syncConfigChildProducts($configurableId, $odooTemplateId);
            }
            $mappingcollection = $this->_productMapping
                ->getCollection()
                ->addFieldToFilter('magento_id', ['eq'=>$productId]);
            if ($mappingcollection) {
                foreach ($mappingcollection as $mapping) {
                    return $mapping->getOdooId();
                }
            }
        } else {
            $response = $this->_productMapResource
                ->createSpecificProduct($productId);
            if ($response['odoo_id'] > 0) {
                return $response['odoo_id'];
            }
        }
        return $odooProductId;
    }

    public function getOdooTaxId($taxCode)
    {
        $odooTaxId = 0;
        if ($taxCode) {
            $collection = $this->_taxRateModel
                ->getCollection()
                ->addFieldToFilter('code', ['eq'=>$taxCode])
                ->getAllIds();

            foreach ($collection as $rateId) {
                $mappingcollection = $this->_taxMapping
                    ->getCollection()
                    ->addFieldToFilter('magento_id', ['eq'=>$rateId]);
                                            
                if (count($mappingcollection)) {
                    foreach ($mappingcollection as $mapping) {
                        $odooTaxId = $mapping->getOdooId();
                    }
                } else {
                    $response = $this->_taxMapResource
                        ->createSpecificTax($rateId);

                    if ($response['odoo_id']) {
                        $odooTaxId = $response['odoo_id'];
                    }
                }
            }
        }
        return (int)$odooTaxId;
    }

    public function getTaxId($mageOrderId)
    {
        $write = $this->_resourceConnection->getConnection('default');
        $tableName = $this->_resourceConnection->getTableName('sales_order_tax');
        $itemTaxes = [];
        $orderTax = $write->query("SELECT code FROM ".$tableName." WHERE order_id= '".$mageOrderId."'");
        $taxCodeResult = $orderTax->fetch();
        if ($taxCodeResult) {
            $taxCode = $taxCodeResult["code"];
            $odooTaxId = $this->getOdooTaxId($taxCode);
            if ($odooTaxId) {
                array_push($itemTaxes, $odooTaxId);
            }
        }
        return $itemTaxes;
    }

    public function createOdooOrderLineVoucherLine($thisOrder, $discountAmount, $odooId, $taxes, $productName)
    {
        $voucherLineId = 0;
        $discountAmount = -(float)$discountAmount;

        $name = "Discount";
        $description = "Discount on ".$productName;
        $voucherLineArray =  [
                'order_id'=>$odooId,
                'name'=>$name,
                'name'=>$description,
                'price_unit'=>$discountAmount
            ];
        if($taxes){
            $voucherLineArray['tax_id'] = $taxes;
        }
        $voucherLineId = $this->syncExtraOdooOrderLine($thisOrder, $voucherLineArray, $description);

        return $voucherLineId;
    }

    public function createOdooOrderVoucherLine($thisOrder, $odooId)
    {
        $voucherLineId = 0;
        $incrementId = $thisOrder->getIncrementId();
        $discountAmount = $thisOrder->getDiscountAmount();

        $description = "Discount";
        $name = "Discount";
        $couponDesc = $thisOrder->getDiscountDescription();
        if ($couponDesc) {
            $description .= "-".$couponDesc;
        }
        $code = $thisOrder->getCouponCode();
        if ($code) {
            $name = "Voucher";
            $description .= " Coupon Code:-".$code;
        }
        
        $voucherLineArray =  [
                'order_id'=>$odooId,
                'name'=>$name,
                'name'=>$description,
                'price_unit'=>$discountAmount
            ];
        $mageOrderId = $thisOrder->getId();
        $voucherLineId = $this->syncExtraOdooOrderLine($thisOrder, $voucherLineArray, $description);

        return $voucherLineId;
    }

    public function createOdooOrderShippingLine($thisOrder, $odooId)
    {
        $helper = $this->_connection;
        $mageOrderId = $thisOrder->getId();
        $shippingDescription = urlencode($thisOrder->getShippingDescription());
        $shippingLineArray =  [
                'order_id'=>$odooId,
                'name'=>'Shipping',
                'description'=>$shippingDescription
            ];
        $shippingIncludesTax = $helper->getStoreConfig('tax/calculation/shipping_includes_tax');
        if ($shippingIncludesTax) {
            $shippingLineArray['price_unit'] = $thisOrder->getShippingInclTax();
        } else {
            $shippingLineArray['price_unit'] = $thisOrder->getShippingAmount();
        }
        if ($thisOrder->getShippingTaxAmount()>0) {
            $shippingTaxes = $this->getMagentoTaxId($mageOrderId, 'shipping');
            if ($shippingTaxes) {
                $shippingLineArray['tax_id'] = $shippingTaxes;
            }
        }

        $shippingLineId = $this->syncExtraOdooOrderLine($thisOrder, $shippingLineArray, $shippingDescription);

        return $shippingLineId;
    }

    public function getMagentoTaxId($orderId, $taxType)
    {
        $taxItems = $this->_taxItemModel
            ->getTaxItemsByOrderId($orderId);
        $odooTaxes = [];
        foreach ($taxItems as $value) {
            if (isset($value['taxable_item_type'])) {
                if ($value['taxable_item_type'] == $taxType) {
                    if (isset($value['code'])) {
                        $odooTaxId = $this->getOdooTaxId($value['code']);
                        array_push($odooTaxes, $odooTaxId);
                    }
                }
            }
        }
        return $odooTaxes;
    }

    public function syncExtraOdooOrderLine($thisOrder, $extraLineArray, $type = "Extra")
    {
        $extraLineId = '';
        $incrementId = $thisOrder->getIncrementId();
        $helper = $this->_connection;
        $extraLineArray['ecommerce_channel'] = 'magento2';
        $resp = $helper->callOdooMethod(
            'wk.skeleton',
            'create_order_shipping_and_voucher_line',
            [$extraLineArray],
            true
        );
        if ($resp && $resp[0]) {
            $odooStatus = $resp[1]['status'];
            if (!$odooStatus) {
                $statusMsg = $resp[1]['status_message'];
                $error = "Line Export Error, Order ".$incrementId." >>".$statusMsg;
                $helper->addError($error);
                return $extraLineId;
            }
            $extraLineId = $resp[1]['order_line_id'];
            $extraLineId = $extraLineId.",";
        } else {
            $respMessage = $resp[1]['status_message'];
            $error = $type." Line Export Error, For Order ".$incrementId." >>".$respMessage;
            $helper->addError($error);
        }
        return $extraLineId;
    }

    public function createOrderMapping($thisOrder, $odooId, $orderName, $partnerId, $lineids = '')
    {
        $mageOrderId = $thisOrder->getId();
        $incrementId = $thisOrder->getIncrementId();
        $helper = $this->_connection;
        $mappingData = [
                'magento_order'=>$incrementId,
                'odoo_id'=>$odooId,
                'odoo_customer_id'=>$partnerId,
                'magento_id'=>$mageOrderId,
                'odoo_line_id'=>rtrim($lineids, ","),
                'odoo_order'=>$orderName,
                'created_by'=>$helper::$mageUser,
            ];
        $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Order::class, $mappingData);
    }

    public function confirmOdooOrder($odooId)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $resp = $helper->callOdooMethod(
            'wk.skeleton',
            'confirm_odoo_order',
            [$odooId],
            true
        );
        if ($resp && $resp[0] == false) {
            $respMessage = $resp[1];
            $error = "Odoo Order ".$odooId." Error During Order Confirm >>".$respMessage;
            $helper->addError($error);
        }
    }

    public function invoiceOdooOrder($thisOrder, $odooId, $invoiceNumber)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $context = $helper->getOdooContext();
        
        $invoiceDate = $thisOrder->getUpdatedAt();
        $incrementId = $thisOrder->getIncrementId();
        $invoice = $thisOrder->getInvoiceCollection()
            ->addFieldToFilter('order_id', $thisOrder->getEntityId())
            ->getData();
        foreach ($invoice as $inv) {
            $invoiceDate = $inv['created_at'];
            if (!$invoiceNumber) {
                $invoiceNumber = $inv['increment_id'];
            }
            break;
        }
        $context['invoice_date'] = $invoiceDate;
        $resp = $helper->callOdooMethod(
            'wk.skeleton',
            'create_order_invoice',
            [$odooId, $invoiceNumber],
            $context
        );
        if ($resp && $resp[0]) {
            $respMessage = $resp[1]['status'];
            if(!$respMessage){
                $status_message = $resp[1]['status_message'];
                $error = "Sync Error, Order ".$incrementId." During Invoice >>".$status_message;
                $this->_connection->addError($error);
                return false;
            } else {
                $invoiceId = $resp[1]['invoice_id'];
                if ($invoiceId > 0) {
                    /**
                    ******** Odoo Order Payment *************
                    */
                    $paymentMethod = $thisOrder->getPayment()->getMethodInstance()->getTitle();
                    $journalId = $this->getOdooPaymentMethod($paymentMethod);
                    $paymentArray = [
                        'order_id'=>$odooId,
                        'journal_id'=>$journalId
                    ];
                    $resp = $helper->callOdooMethod(
                        'wk.skeleton',
                        'set_order_paid',
                        [$paymentArray],
                        true
                    );
                    if ($resp && $resp[0]) {
                        $respMessage = $resp[1]['status'];
                        if(!$respMessage){
                            $status_message = $resp[1]['status_message'];
                            $error = "Sync Error, Order ".$incrementId." During Payment >>".$status_message;
                            $this->_connection->addError($error);
                            return false;
                        } else {
                            return true;
                        }
                    } else {
                        $respMessage = $resp[1];
                        $error = "Sync Error, Order ".$incrementId." During Payment >>".$respMessage;
                        $helper->addError($error);
                    }
                } elseif ($invoiceId == 0) {
                    $error = "Sync Error, Order ".$incrementId." During Invoice >> Not able to create invoice at odoo.";
                    $this->_connection->addError($error);
                }
            }
        } else {
            $respMessage = $resp[1];
            $error = "Sync Error, Order ".$incrementId." During Invoice >>".$respMessage;
            $helper->addError($error);
        }
        return true;
    }

    public function deliverOdooOrder($thisOrder, $erpOrderId, $shipmentObj = false)
    {
        $shipmentNo = false;
        $tracknums = false;
        $trackCarrier = false;
        $helper = $this->_connection;
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $incrementId = $thisOrder->getIncrementId();
        if ($shipmentObj) {
            $shipmentNo = $shipmentObj->getId();
            foreach ($shipmentObj->getAllTracks() as $tracknum) {
                $tracknums=$tracknum->getTrackNumber();
                $trackCarrier=$tracknum->getCarrierCode();
                break;
            }
        } else {
            $shipment = $thisOrder->getShipmentsCollection();
            foreach ($shipment as $ship) {
                $shipmentNo = $ship->getId();
                foreach ($ship->getAllTracks() as $tracknum) {
                    $tracknums=$tracknum->getTrackNumber();
                    $trackCarrier=$tracknum->getCarrierCode();
                    break;
                }
                break;
            }
        }
        $context['ship_number'] = $shipmentNo;
        if($trackCarrier && $tracknums){
            $context['carrier_tracking_ref'] = $tracknums;
            $context['carrier_code'] = $trackCarrier;
        }
        $resp = $helper->callOdooMethod(
            'wk.skeleton',
            'set_order_shipped',
            [$erpOrderId],
            $context
        );
        if ($resp && $resp[0]) {
            $respMessage = $resp[1]['status'];
            if(!$respMessage){
                $status_message = $resp[1]['status_message'];
                $error = "Sync Error, Order ".$incrementId." During Shipment >>".$status_message;
                $this->_connection->addError($error);
                return false;
            } else {
                return true;
            }
        } else {
            $respMessage = $resp[1];
            $error = "Sync Error, Order ".$incrementId." During Shipment >>".$respMessage;
            $helper->addError($error);
            return false;
        }
        return true;
    }

    public function getOdooPaymentMethod($paymentMethod)
    {
        $mappingcollection = $this->_paymentMapping
            ->getCollection()
            ->addFieldToFilter('magento_id', $paymentMethod);
        if (count($mappingcollection) > 0) {
            foreach ($mappingcollection as $map) {
                return (int)$map->getOdooId();
            }
        } else {
            $response = $this->_paymentMapResource
                ->syncSpecificPayment($paymentMethod);
            $odooPaymentId = $response['odoo_id'];
            return (int)$odooPaymentId;
        }
    }

    public function getOdooOrderAddresses($thisOrder)
    {
        $partnerId = 0;
        $partnerInvoiceId = 0;
        $partnerShippingId = 0;
        $billingAddresssId = 0;
        $shippingAddressId = 0;
        $storeId = $thisOrder->getStoreId();
        $customerId = $thisOrder->getCustomerId();
        $billing = $thisOrder->getBillingAddress();
        $shipping = $thisOrder->getShippingAddress();
        $magerpsync = $this->_customerMapping;
        if ($billing) {
            $billing->setEmail($thisOrder->getCustomerEmail());
        }
        if ($shipping) {
            $shipping->setEmail($thisOrder->getCustomerEmail());
        }
        $customerArray =  [
            'name'=>urlencode($thisOrder->getCustomerName()),
            'email'=>urlencode($thisOrder->getCustomerEmail()),
            'is_company'=>false,
        ];
        if ($thisOrder->getCustomerIsGuest() == 1) {
            $customerId = 0;
            $customerArray['name'] = urlencode($billing->getName());
        }
        if ($customerId > 0) {
            $billingAddresssId =  $billing->getCustomerAddressId();
            if (!$billingAddresssId) {
                $billingAddresssId = $this->getMagentoCustomerAddressId($customerId, true);
            }
            if ($shipping) {
                $shippingAddressId = $shipping->getCustomerAddressId();
                if (!$shippingAddressId) {
                    $shippingAddressId = $this->getMagentoCustomerAddressId($customerId);
                }
            }
            $mappingcollection = $this->_customerModel
                                        ->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$customerId])
                                        ->addFieldToFilter('address_id', ['eq'=>"customer"]);
            if (count($mappingcollection)>0) {
                foreach ($mappingcollection as $map) {
                    $partnerId = $map->getOdooId();
                    break;
                }
            }
        }
        if (!$partnerId) {
            $partnerId = $this->_customerMapResource->odooCustomerCreate($customerArray, $customerId, 'customer', $storeId);
        }
        if ($partnerId){
            $partnerInvoiceId = $this->createOdooAddress(
                $billing, 
                $partnerId, 
                $customerId, 
                $billingAddresssId, 
                $storeId
            );
            $isDifferent = $this->checkAddresses($thisOrder);
            if ($isDifferent == true && $shipping) {
                $partnerShippingId = $this->createOdooAddress(
                    $shipping,
                    $partnerId,
                    $customerId,
                    $shippingAddressId,
                    $storeId
                );
                
            } else {
                $partnerShippingId = $partnerInvoiceId;
            }
        }

        return [(int)$partnerId, (int)$partnerInvoiceId, (int)$partnerShippingId];
    }

    public function getMagentoCustomerAddressId($customerId, $isInvoice=false)
    {
        $customerAddressId = 0;
        $customer = $this->_customerObj->load($customerId);
        $addressId = $customer->getDefaultShipping();
        if ($isInvoice) {
            $addressId = $customer->getDefaultBilling();
        }
        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                $customerAddressId = $address->getEntityId();
                break;
            }
        }
        return $customerAddressId;
    }

    public function createOdooAddress($flatAddress, $parentId, $mageCustomerId, $mageAddressId, $storeId = 0)
    {
        $flag = false;
        $odooCusId = 0;
        $addressArray = [];
        $addressArray = $this->customerAddressArray($flatAddress);
        
        if ($mageAddressId > -1) {
            $addresscollection =  $this->_customerMapping
            ->getCollection()
            ->addFieldToFilter('magento_id', ['eq'=>$mageCustomerId])
            ->addFieldToFilter('address_id', ['eq'=>$mageAddressId]);
            
            if (count($addresscollection)>0) {
                foreach ($addresscollection as $add) {
                    $mapId = $add->getEntityId();
                    $odooCusId = $add->getOdooId();
                }
            } else {
                $flag = true;
            }
        } else {
            $flag = true;
        }
        if ($flag == true) {

            if ($addressArray) {
                $addressArray['parent_id'] = (int)$parentId;
                $odooCusId = $this->_customerMapResource
                    ->odooCustomerCreate($addressArray, $mageCustomerId, $mageAddressId, $storeId);
            }
        }
        return (int)$odooCusId;
    }

    public function customerAddressArray($flatAddress)
    {
        $type = '';
        $addressArray = [];
        if ($flatAddress['address_type'] == 'billing') {
            $type = 'invoice';
        }
        if ($flatAddress['address_type'] == 'shipping') {
            $type = 'delivery';
        }
        $streets = $flatAddress->getStreet();
        if (count($streets)>1) {
            $street = urlencode($streets[0]);
            $street2 = urlencode($streets[1]);
        } else {
            $street = urlencode($streets[0]);
            $street2 = urlencode('');
        }

        $addressArray =  [
            'name'=>urlencode($flatAddress->getName()),
            'street'=>$street,
            'street2'=>$street2,
            'city'=>urlencode($flatAddress->getCity()),
            'email'=>urlencode($flatAddress->getEmail()),
            'zip'=>$flatAddress->getPostcode(),
            'phone'=>$flatAddress->getTelephone(),
            'country_code'=>$flatAddress->getCountryId(),
            'region'=>urlencode($flatAddress->getRegion()),
            'wk_company'=>urlencode($flatAddress->getCompany()),
            'customer_rank'=>false,
            'type'=>$type
        ];
        return $addressArray;
    }

    public function checkAddresses($thisOrder)
    {
        $flag = false;
        if ($thisOrder->getShippingAddressId() && $thisOrder->getBillingAddressId()) {
            $s = $thisOrder->getShippingAddress();
            $b = $thisOrder->getBillingAddress();
            if ($s['street'] != $b['street']) {
                $flag = true;
            }
            if ($s['postcode'] != $b['postcode']) {
                $flag = true;
            }
            if ($s['city'] != $b['city']) {
                $flag = true;
            }
            if ($s['region'] != $b['region']) {
                $flag = true;
            }
            if ($s['country_id'] != $b['country_id']) {
                $flag = true;
            }
            if ($s['firstname'] != $b['firstname']) {
                $flag = true;
            }
        }
        return $flag;
    }

    public function checkOdooOrderStatus($odooId, $key)
    {
        $isStatus = false;
        $helper = $this->_connection;
        $resp = $helper->callOdooMethod('sale.order', 'read', [$odooId, [$key, 'state']]);
        if ($resp && $resp[0]) {
            $isStatus = $resp[1][0][$key];
        } else {
            $faultString = $resp[1];
            $helper->addError($faultString);
        }
        return $isStatus;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_order', 'entity_id');
    }
}
