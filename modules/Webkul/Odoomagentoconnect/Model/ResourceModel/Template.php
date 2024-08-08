<?php
/**
 * Webkul Odoomagentoconnect Template ResourceModel
 *
 * @author    Webkul
 * @api
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableModel,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockApi,
        \Webkul\Odoomagentoconnect\Model\Product $productMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product $productMapResource,
        \Webkul\Odoomagentoconnect\Model\Option $optionMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Attribute $attributeMapResource,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_eventManager = $eventManager;
        $this->_productModel = $productModel;
        $this->_configurableModel = $configurableModel;
        $this->_stockApi = $stockApi;
        $this->_productMapping = $productMapping;
        $this->_productMapResource = $productMapResource;
        $this->_optionMapping = $optionMapping;
        $this->_attributeMapResource = $attributeMapResource;
        $this->_connection = $connection;
        $this->_scopeConfig = $scopeConfig;
    }

    public function updateMapping($model, $status = 'yes')
    {
        $model->setNeedSync($status);
        $model->save();
        return true;
    }

    public function syncConfigurableProduct($mappingObj, $proId)
    {
        if ($mappingObj) {
            $this->updateConfigurableProduct($mappingObj);
        } else {
            $response = $this->exportSpecificConfigurable($proId);
            if ($response['odoo_id'] > 0) {
                $erpTemplateId = $response['odoo_id'];
                $this->syncConfigChildProducts($proId, $erpTemplateId);
            }
        }
        return true;
    }

    public function exportSpecificConfigurable($configurableId)
    {
        $response = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        if ($configurableId) {
            $context = $helper->getOdooContext();
            $childIds = $this->_configurableModel->getChildrenIds($configurableId);
            if (!$childIds[0]) {
                $errorMsg = "Product Export Error, Product Id ".$configurableId.", No Child Product Exists!!!";
                $helper->addError($errorMsg);
                return [
                    'error' => $errorMsg,
                    'odoo_id' => -1
                ];
            }
            $configurableArray = $this->_productMapResource->getProductArray($configurableId);
            $attributes = $this->odooAttributeList($configurableId);
            $configurableArray['attribute_list'] = $attributes;
            $context['configurable'] = 'configurable';
            $context['create_product_product'] = true;
            $product = $this->_productModel->load($configurableId);
            if (isset($product->getData()['price'])) {
                $productPrice = $product->getData()['price'];
                $configurableArray['list_price'] = (float)$productPrice;
            }
            $resp = $helper->callOdooMethod('product.template', 'create', [$configurableArray], $context);
            if ($resp && $resp[0]) {
                $odooId = $resp[1];
                if ($odooId > 0) {
                    $mappingData = [
                        'odoo_id'=>$odooId,
                        'magento_id'=>$configurableId,
                        'created_by'=>$helper::$mageUser
                    ];
                    $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Template::class, $mappingData);
                    $response['odoo_id'] = $odooId;
                    $dispatchData = ['product' => $configurableId, 'erp_product' => $odooId, 'type' => 'template'];
                    $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
                }
            } else {
                $respMessage = $resp[1];
                $error = "Export Error, Product Id ".$configurableId." >> ".$respMessage;
                $helper->addError($error);
                $response['odoo_id'] = 0;
                $response['error'] = $respMessage;
            }
        }
        return $response;
    }

    public function updateConfigurableProduct($mappingId)
    {
        $response = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        if ($mappingId) {
            $template =  $mappingId->getData();
            $configurableId = $template['magento_id'];
            $odooTemplateId = (int)$template['odoo_id'];

            $configurableArray = $this->_productMapResource->getProductArray($configurableId);
            $product = $this->_productModel->load($configurableId);
            if (isset($product->getData()['price'])) {
                $productPrice = $product->getData()['price'];
                $configurableArray['list_price'] = (float)$productPrice;
            }
            $resp = $helper->callOdooMethod('product.template', 'write', [$odooTemplateId, $configurableArray]);
            if ($resp && $resp[0]) {
                $response['odoo_id'] = $odooTemplateId;
                $this->syncConfigChildProducts($configurableId, $odooTemplateId);
                $this->updateMapping($mappingId, 'no');
                $dispatchData = ['product' => $configurableId, 'erp_product' => $odooTemplateId, 'type' => 'template'];
                $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
            } else {
                $respMessage = $resp[1];
                $error = "Configurable Update Error, Product Id ".$configurableId." >> ".$respMessage;
                $helper->addError($error);
                $response['odoo_id'] = 0;
                $response['error'] = $respMessage;
            }
        }
        return $response;
    }

    public function syncConfigChildProducts($configurableId, $odooTemplateId)
    {
        if ($configurableId) {
            $template = $this->_productModel->load($configurableId);
            $templatePrice = $template->getPrice();
            if (isset($template->getData()['price'])) {
                $templatePrice = $template->getData()['price'];
            }
            $attrCodes = $this->productAttributeLine($configurableId, $odooTemplateId);
            if ($attrCodes) {
                $childIds = $this->_configurableModel
                    ->getChildrenIds($configurableId);
                foreach ($childIds[0] as $childId) {
                    $mappingCollection = $this->_productMapping->getCollection()
                        ->addFieldToFilter('magento_id', ['eq'=>$childId]);
                    if ($mappingCollection->getSize() > 0) {
                        foreach ($mappingCollection as $map) {
                            $mappingId = $map->getEntityId();
                            $this->updateChildProduct($mappingId, $odooTemplateId, $attrCodes, $templatePrice);
                        }
                    } else {
                        $this->exportChildProduct($odooTemplateId, $childId, $attrCodes, $templatePrice);
                    }
                }
            }
        }
        return true;
    }

    public function exportChildProduct($erpTmplId, $childId, $attrCodes, $templatePrice)
    {
        $response = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $context = $helper->getOdooContext();
        $productArray = $this->_productMapResource->getProductArray($childId);

        $product = $this->_productModel->load($childId);
        $productPrice = $product->getPrice();
        $variantExtraPrice = $productPrice - $templatePrice;
        $attributeValueIds = [];
        foreach ($attrCodes as $key) {
            $optionid =  $product->getData($key);
            $optionCollection = $this->_optionMapping->getCollection()
                                                ->addFieldToFilter('magento_id', $optionid);
            foreach ($optionCollection as $value) {
                $erpValueId = (int)$value->getOdooId();
                array_push($attributeValueIds, $erpValueId);
            }
        }
        if ($attributeValueIds) {
            $productArray['value_ids'] = $attributeValueIds;
        }
        if ($erpTmplId) {
            $productArray['product_tmpl_id'] = $erpTmplId;
        }
        if ($variantExtraPrice) {
            $productArray['wk_extra_price'] = $variantExtraPrice;
        }
        if (isset($productArray['name'])) {
            unset($productArray['name']);
        }
        if (isset($productArray['list_price'])) {
            unset($productArray['list_price']);
        }
        $itemId = $this->_stockApi
                    ->getStockItem($childId)->getId();
        $context['magento_stock_id'] = $itemId;
        $resp = $helper->callOdooMethod('product.product', 'create', [$productArray], $context);
        if ($resp && $resp[0]) {
            $odooId = $resp[1];
            if ($odooId > 0) {
                $mappingData = [
                    'odoo_id'=>$odooId,
                    'magento_id'=>$childId,
                    'created_by'=>$helper::$mageUser
                ];
                $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Product::class, $mappingData);
                $response['odoo_id'] = $odooId;
                $syncStock = $this->_scopeConfig->getValue('odoomagentoconnect/automatization_settings/auto_inventory');
                if ($syncStock) {
                    $this->_productMapResource
                            ->createInventoryAtOdoo($childId, $odooId);
                }
                $dispatchData = ['product' => $childId, 'erp_product' => $odooId, 'type' => 'product'];
                $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
            }
        } else {
            $respMessage = $resp[1];
            $error = "Export Error, Product Id ".$childId." >> ".$respMessage;
            $helper->addError($error);
            $response['odoo_id'] = 0;
            $response['error'] = $respMessage;
        }
        return $response;
    }

    public function productAttributeLine($configurableId, $odooTemplateId)
    {
        $attrCodes = [];
        $helper = $this->_connection;
        $context = $helper->getOdooContext();
        $_product = $this->_productModel->load($configurableId);
        $attributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
        foreach ($attributes as $attribute) {
            $attributeArray = [];
            $attributeId = $attribute['attribute_id'];
            $attributeCode = $attribute['attribute_code'];
            array_push($attrCodes, $attributeCode);
            $response = $this->_attributeMapResource
                ->syncAttribute($attributeId);
            $odooAttributeId = (int)$response['odoo_attribute_id'];
            if ($odooAttributeId) {
                $valueArray = [];
                $attributeOptions = $attribute['values'];
                $attributeArray['attribute_id'] = $odooAttributeId;
                $attributeArray['product_tmpl_id'] = $odooTemplateId;
                foreach ($attributeOptions as $option) {
                    $optionId = $option['value_index'];
                    $priceExtra = 0;
                    $optionCollection = $this->_optionMapping
                        ->getCollection()
                        ->addFieldToFilter('magento_id', $optionId);
                    foreach ($optionCollection as $value) {
                        $attrValueId = (int)($value->getOdooId());
                        $value = [
                            'value_id'=>$attrValueId,
                            'price_extra'=>$priceExtra,
                        ];
                        array_push($valueArray, $value);
                        break;
                    }
                }
                $attributeArray['values'] = $valueArray;
            }
            if ($attributeArray) {
                $resp = $helper->callOdooMethod('connector.template.mapping', 'create_n_update_attribute_line', [$attributeArray], $context);
            }
        }
        return $attrCodes;
    }

    public function odooAttributeList($configurableId)
    {
        $odooAttributes = [];
        $_product = $this->_productModel->load($configurableId);
        $attributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
        foreach ($attributes as $attribute) {
            $attributeId = $attribute['attribute_id'];
            $response = $this->_attributeMapResource
                ->syncAttribute($attributeId);
            $odooAttributeId = $response['odoo_attribute_id'];
            if ($odooAttributeId) {
                array_push($odooAttributes, (int)$odooAttributeId);
            }
        }
        return $odooAttributes;
    }

    public function updateChildProduct($mappingId, $erpTemplateId, $attrCodes, $templatePrice)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        if ($mappingId) {
            $context = $helper->getOdooContext();
            $mapping =  $this->_productModel
                                ->load($mappingId);
            $mappingData = $mapping->getData();
            $odooId = $mappingData['odoo_id'];
            $mageId = $mappingData['magento_id'];
            
            $productArray = $this->_productMapResource->getProductArray($mageId);
            $product = $this->_productModel->load($mageId);
            $productPrice = $product->getPrice();
            $variantExtraPrice = $productPrice - $templatePrice;
            $attributeValueIds = [];
            foreach ($attrCodes as $key) {
                $optionid =  $product->getData($key);
                $optionCollection = $this->_optionMapping->getCollection()
                                                    ->addFieldToFilter('magento_id', $optionid);
                foreach ($optionCollection as $value) {
                    $erpValueId = $value->getOdooId();
                    array_push($attributeValueIds, $erpValueId);
                }
            }
            if ($attributeValueIds) {
                $productArray['value_ids'] = $attributeValueIds;
            }
            if ($erpTemplateId) {
                $productArray['product_tmpl_id'] = $erpTemplateId;
            }
            if ($variantExtraPrice) {
                $productArray['wk_extra_price'] = $variantExtraPrice;
            }
            if (isset($productArray['name'])) {
                unset($productArray['name']);
            }
            if (isset($productArray['list_price'])) {
                unset($productArray['list_price']);
            }
            $context['create_product_variant'] = 'create_product_variant';
            $resp = $helper->callOdooMethod('product.product', 'write', [$odooId, $productArray], $context);
            if ($resp && $resp[0]) {
                $this->updateMapping($mappingId, 'no');
                $dispatchData = ['product' => $mageId, 'erp_product' => $odooId, 'type' => 'product'];
                $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
                return true;
            } else {
                $respMessage = $resp[1];
                $error = "Variant Update Error, Product Id ".$mageId." >> ".$respMessage;
                $helper->addError($error);
            }
        }
        return false;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_template', 'entity_id');
    }
}
