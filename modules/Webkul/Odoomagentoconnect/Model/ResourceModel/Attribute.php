<?php
/**
 * Webkul Odoomagentoconnect Attribute ResourceModel
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Attribute ResourceModel Class
 */
class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime       $date
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Option $optionMapResource,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        $resourcePrefix = null
    ) {
        $this->_productModel = $productModel;
        $this->_attributeCollection = $attributeCollection;
        $this->_optionMapResource = $optionMapResource;
        $this->_connection = $connection;
        parent::__construct($context, $resourcePrefix);
    }

    public function getMageAttributeArray()
    {
        $productAttribute = [];
        $productAttribute[''] ='--Select Magento Product Attribute--';
        $collection = $this->_attributeCollection->addVisibleFilter();
        $collection->getSelect()->where('is_user_defined = 1');
        $collection->getSelect()->where('is_global = 1');

        foreach ($collection as $value) {
            if ($value['frontend_input']=='select') {
                $mage_attribute_id = $value->getAttributeId();
                $mage_attribute_label = $value->getAttributeCode();
                $productAttribute[$mage_attribute_id] = $mage_attribute_label;
            }
        }

        return $productAttribute;
    }

    public function getOdooAttributeArray()
    {
        $attributeArray = [];
        $helper = $this->_connection;
        $resp = $helper->callOdooMethod('product.attribute', 'search_read', [[],['id', 'name']]);
        if ($resp && $resp[0]) {
            $attributeArray[''] ='--Select Odoo Attribute--';
            $odooAttributes = $resp[1];
            foreach ($odooAttributes as $odooAttribute) {
                $attributeArray[$odooAttribute['id']] = $odooAttribute['name'];
            }
        } else {
            $attributeArray['error'] = $resp[1];
        }
        return $attributeArray;
    }

    public function syncAttributeSets($setName, $setId, $erpAttributeIds)
    {
        $helper = $this->_connection;
        $setArray = [
            'name'=>$setName,
            'set_id'=>$setId,
        ];
        if (!empty($erpAttributeIds)) {
            $setArray['attribute_ids']= $erpAttributeIds;
        }
        $resp = $helper->callOdooMethod('connector.snippet', 'sync_attribute_set', [$setArray]);
        return true;
    }

    public function updateAttribute()
    {
        $attributemodel = $this->_connection->getModel(\Webkul\Odoomagentoconnect\Model\Attribute::class);
        $collection = $attributemodel->getCollection();
        $updatedAttribute = 0;
        $notUpdatedAttribute = 0;
        foreach ($collection as $attributeMapModel) {
            $mageId = $attributeMapModel->getMagentoId();
            $odooId = $attributeMapModel->getOdooId();
            $response = $this->syncAttribute($mageId);
            if ($response['odoo_attribute_id'] == 0) {
                $notUpdatedAttribute++;
            } else {
                $updatedAttribute++;
            }
        }
        return [$updatedAttribute, $notUpdatedAttribute];
    }

    public function syncAttribute($attributeId)
    {
        $odooId = 0;
        $response = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();

        $mappingcollection =  $helper->getModel(\Webkul\Odoomagentoconnect\Model\Attribute::class)
            ->getCollection()
            ->addFieldToFilter('magento_id', $attributeId);
        if ($mappingcollection->getSize() > 0) {
            foreach ($mappingcollection as $map) {
                $odooId = (int)$map->getOdooId();
                break;
            }
        } else {
            $collection = $this->_productModel->getResource()
                ->getAttribute($attributeId);
            $code = $collection->getAttributeCode();
            $attributeArray = [
                'name' => $collection->getFrontend()->getLabel(),
            ];
            $resp = $helper->callOdooMethod('product.attribute', 'create', [$attributeArray]);
            if ($resp && $resp[0]) {
                $odooId = $resp[1];
                $mappingData = [
                    'name'=>$code,
                    'odoo_id'=>$odooId,
                    'magento_id'=>$attributeId,
                    'created_by'=>$helper::$mageUser
                ];
                $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Attribute::class, $mappingData);
                $mappingData['ecomm_attribute_code']=$code;
                $this->mapAttribute($mappingData);
            } else {
                $respMessage = $resp[1];
                $error = "Export Error, Attribute Id ".$attributeId." >> ".$respMessage;
                $helper->addError($error);
                $response['error'] = $respMessage;
            }
        }
        $response['odoo_attribute_id'] = $odooId;
        if ($odooId) {
            $response['optcount'] = $this->_optionMapResource->syncAllAttributeOptions($attributeId, $odooId);
        }
        return $response;
    }

    public function mapAttribute($data)
    {
        $helper = $this->_connection;
        $attrMappingArray = [
                        'name'=>$data['odoo_id'],
                        'odoo_id'=>$data['odoo_id'],
                        'ecomm_id'=>$data['magento_id'],
                        'ecomm_attribute_code'=>$data['ecomm_attribute_code'],
                        'created_by'=>$helper::$mageUser,
                    ];
        $resp = $helper->callOdooMethod('connector.attribute.mapping', 'create', [$attrMappingArray], true);
        /*if ($resp->faultCode()) {
            return false;
        }*/
        return true;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_attribute', 'entity_id');
    }
}
