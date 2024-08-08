<?php
/**
 * Webkul Odoomagentoconnect Payment ResourceModel
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Product ResourceModel Class
 */
class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockApi,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Set $setmapping,
        \Webkul\Odoomagentoconnect\Model\Category $categorymapping,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_directoryList = $directoryList;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_productModel = $productModel;
        $this->_stockApi = $stockApi;
        $this->_connection = $connection;
        $this->_setmapping = $setmapping;
        $this->_scopeConfig = $scopeConfig;
        $this->_categorymapping = $categorymapping;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
    }

    public function getMageProductArray()
    {
        $product = [];
        $product[''] ='--Select Magento Product--';
        $collection = $this->_productModel
            ->getCollection()
            ->addAttributeToFilter('type_id', ['neq' => 'configurable'])
            ->addAttributeToSelect('name');
        foreach ($collection as $productObj) {
            $productId = $productObj->getId();
            $productName = $productObj->getName();
            $productSku = $productObj->getSku();
            if ($productSku) {
                $productName = "[$productSku] $productName";
            }
            $product[$productId] = $productName;
        }
        return $product;
    }

    public function getOdooProductArray()
    {
        $productData = [];
        $helper = $this->_connection;
        $params = [
            [['product_template_attribute_value_ids', '=', false]], // Domain
            ['id', 'display_name'] // Fields
        ];
        $resp = $helper->callOdooMethod('product.product', 'search_read', $params);
        if ($resp && $resp[0]) {
            $productData[''] ='--Select Odoo Product--';
            $odooProducts = $resp[1];
            foreach ($odooProducts as $odooProduct) {
                $productData[$odooProduct['id']] = $odooProduct['display_name'];
            }
        } else {
            $productData['error'] = $resp[1];
        }
        return $productData;
    }

    public function syncSimpleProduct($visibility, $parentIds, $mappingObj, $proId)
    {
        if (!$parentIds && $visibility != 1) {
            if ($mappingObj) {
                $this->updateNormalProduct($mappingObj);
            } else {
                $response = $this->createSpecificProduct($proId);
            }
        }
        return true;
    }

    public function createSpecificProduct($mageId)
    {
        $response = [];
        $helper = $this->_connection;
        if ($mageId) {
            $context = $helper->getOdooContext();
            $productArray = $this->getProductArray($mageId);
            $context['magento_stock_id'] = $this->_stockApi->getStockItem($mageId)->getId();
            $resp = $helper->callOdooMethod('product.product', 'create', [$productArray], $context);
            if ($resp && $resp[0]) {
                $odooId = $resp[1];
                if ($odooId > 0) {
                    $mappingData = [
                        'odoo_id'=>$odooId,
                        'magento_id'=>$mageId,
                        'created_by'=>$helper::$mageUser
                    ];
                    $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Product::class, $mappingData);
                    $response['odoo_id'] = $odooId;
                    $syncStock = $this->_scopeConfig
                    ->getValue('odoomagentoconnect/automatization_settings/auto_inventory');
                    if ($syncStock) {
                        $this->createInventoryAtOdoo($mageId, (int)$odooId);
                    }
                    $dispatchData = ['product' => $mageId, 'odoo_product' => $odooId, 'type' => 'product'];
                    $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
                }
            } else {
                $respMessage = $resp[1];
                $error = "Export Error, Product Id ".$mageId." >> ".$respMessage;
                $helper->addError($error);
                $response['odoo_id'] = 0;
                $response['error'] = $respMessage;
            }
        }
        return $response;
    }

    public function getProductCategoryArray($categoryIds)
    {
        $odooCategories = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        foreach ($categoryIds as $catId) {
            $mapcollection = $this->_categorymapping->getCollection()
                                ->addFieldToFilter('magento_id', ['eq'=>$catId]);
            foreach ($mapcollection as $map) {
                $odooId = $map->getOdooId();
                array_push($odooCategories, $odooId);
            }
        }
        if (!$odooCategories) {
            $defaultCategory = $helper->getSession()->getOdooCateg();
            array_push($odooCategories, $defaultCategory);
        }
        return $odooCategories;
    }

    public function getProductArray($productId)
    {
        $type = 'product';
        $product = $this->_productModel->load($productId);
        $ean = $product->getEan();
        $keys = ['simple','grouped','configurable','virtual','bundle','downloadable'];
        $productType = $product->getTypeId();
        if (!in_array($productType, $keys)) {
            $productType = "";
        }
        if (!in_array($productType, ['simple', 'configurable'])) {
            $type = 'service';
        }
        $status = true;
        $status = urlencode($product->getStatus());
        if ($status == '2') {
            $status = false;
        }
        $sku = urlencode($product->getSku());
        $productArray = [
                'type'=>$type,
                'default_code'=>$sku,
                'ecomm_id'=>$productId,
                'sale_ok'=>$status,
                'weight'=>$product->getWeight(),
                'standard_price'=>$product->getCost(),
        ];
        $parentIds = $this->_catalogProductTypeConfigurable->getParentIdsByChild($productId);
        if (!isset($parentIds[0])) {
            $name = urlencode($product->getName());
            $description = urlencode($product->getDescription());
            $shortDescription = urlencode($product->getShortDescription());
            $odooCategoryArray = $this->getProductCategoryArray($product->getCategoryIds());
            $setId = $product->getAttributeSetId();
            $odooSetId = $this->_setmapping->getOdooAttributeSetId($setId);

            $productArray['name'] = $name;
            $productArray['attribute_set_id'] = $odooSetId;
            $productArray['description'] = $description;
            $productArray['description_sale'] = $shortDescription;
            $productArray['list_price'] = $product->getPrice();
            $productArray['prod_type'] = $productType;
            $productArray['category_ids'] = $odooCategoryArray;
        }


        if ($product->getImage()) {
            if ($product->getImage() != "no_selection") {
                try {
                    $baseImage = 'catalog/product'.$product->getImage();
                    $imagePath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$baseImage;
                    $productImagePath = $this->_directoryList->getPath('media').'/'.$baseImage;
                    if (file_exists($productImagePath)) {
                        $imageUrl = $imagePath;
                    } else {
                        $imageUrl = false;
                    }
                } catch (\Exception $e) {
                    $imageUrl = false;
                }
                if ($imageUrl) {
                    $productArray['image_url'] = $imageUrl;
                }
            }
        }
        if ($ean) {
            $productArray['barcode'] = $ean;
        }
        return $productArray;
    }

    public function updateNormalProduct($mappingObj)
    {
        $response = [];
        $helper = $this->_connection;
        if ($mappingObj) {
            $mapping =  $mappingObj->getData();
            $odooId = (int)$mapping['odoo_id'];
            $mageId = $mapping['magento_id'];
            $productArray = $this->getProductArray($mageId);
            unset($productArray['type']);
            $resp = $helper->callOdooMethod('product.product', 'write', [$odooId, $productArray], true);
            if ($resp && $resp[0]) {
                $response['odoo_id'] = $odooId;
                $this->updateMapping($mappingObj, 'no');
                $dispatchData = ['product' => $mageId, 'erp_product' => $odooId, 'type' => 'product'];
                $this->_eventManager->dispatch('catalog_product_sync_after', $dispatchData);
            } else {
                $respMessage = $resp[1];
                $error = "Product Update Error, Product Id ".$mageId." >> ".$respMessage;
                $helper->addError($error);
                $response['odoo_id'] = 0;
                $response['error'] = $respMessage;
            }
        }
        return $response;
    }

    public function createInventoryAtOdoo($mageProdId, $odooProId)
    {
        $helper = $this->_connection;
        $productQty = $this->_stockApi
            ->getStockItem($mageProdId)->getQty();
        if ($productQty > 0) {
            return false;
        }
        $inventoryArray = [
            'product_id'=>(int)$odooProId,
            'new_quantity'=>(int)$productQty
        ];
        $resp = $helper->callOdooMethod('connector.snippet', 'update_quantity', [$inventoryArray], ['stock_from' => 'magento']);
        if ($resp && $resp[0]) {
            return true;
        } else {
            $respMessage = $resp[1];
            $error = "Stock update error, Product Id ".$mageProdId." >> ".$respMessage;
            $helper->addError($error);
        }
        return true;
    }

    public function updateMapping($model, $status = 'yes')
    {
        $model->setNeedSync($status);
        $model->save();
        return true;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_product', 'entity_id');
    }
}
