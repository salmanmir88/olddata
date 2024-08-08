<?php
/**
 * Webkul Odoomagentoconnect Set ResourceModel
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

class Set extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $setInterface,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Attribute $attributeObj,
        \Magento\Catalog\Model\Product $catalogModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $resourcePrefix = null
    ) {
        $this->_catalogModel = $catalogModel;
        $this->_attributeObj = $attributeObj;
        $this->_connection = $connection;
        $this->_setInterface = $setInterface;
        parent::__construct($context, $resourcePrefix);
        $this->_objectManager = $objectManager;
    }

    public function getOdooAttributeSetId($setId)
    {
        $odooAttributeSetId = 0;
        $mappingcollection =  $this->_connection->getModel(\Webkul\Odoomagentoconnect\Model\Set::class)
            ->getCollection()
            ->addFieldToFilter('magento_id', $setId);
        if ($mappingcollection->getSize() > 0) {
            foreach ($mappingcollection as $map) {
                $odooAttributeSetId = $map->getOdooId();
            }
        } else {
            $attributeSet = $this->_setInterface->get($setId);
            $setName = $attributeSet->getAttributeSetName();
            $response = $this->exportAttributeSet($setName, $setId);
            if ($response['success']) {
                $result = $this->syncConfigurableAttributes($setName, $setId);
            }
            if (isset($response['odoo_id'])) {
                $odooAttributeSetId = $response['odoo_id'];
            }
        }
        return $odooAttributeSetId;
    }

    public function getMappedAttributeSetIds()
    {
        $mappedIds = [];
        $mappingCollection =  $this->_connection->getModel(\Webkul\Odoomagentoconnect\Model\Set::class)
                                    ->getCollection()
                                    ->addFieldToSelect('magento_id');
        
        foreach ($mappingCollection as $mapping) {
            array_push($mappedIds, $mapping->getMagentoId());
        }
        return $mappedIds;
    }

    public function exportAttributeSet($setName, $setId)
    {
        $helper = $this->_connection;
        $odooId = 0;
        $success = true;
        $mappedIds = $this->getMappedAttributeSetIds();
        if (!in_array($setId, $mappedIds)) {
            $userId = $helper->getSession()->getUserId();
            $attributesetArray = [
                'name'=> $setName,
                'set_id'=> $setId,
                'created_by'=> "magento",
            ];
            $resp = $helper->callOdooMethod('magento.attribute.set', 'create', [$attributesetArray]);
            if ($resp && $resp[0]) {
                $odooId = $resp[1];
                if ($odooId > 0) {
                    $mappingData = [
                        'name'=> $setName,
                        'odoo_id'=>$odooId,
                        'magento_id'=>$setId,
                        'created_by'=>$helper::$mageUser
                    ];
                    $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Set::class, $mappingData);
                }
            } else {
                $respMessage = $resp[1];
                $error = "Export Error, Attribute Set Id ".$setId." >> ".$respMessage;
                $helper->addError($error);
                $success = false;
            }
        } else {
            $odooId = $setId;
        }
        return [
            'success'=> $success,
            'odoo_id'=>$odooId,
        ];
    }

    public function syncConfigurableAttributes($setName, $setId)
    {
        $attr = 0;
        $fails = '';
        $optcount = 0;
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $attributeArray = [];
        $attributeModel = $this->_attributeObj;
        $attributes = $this->_catalogModel->getResource()
                                                       ->loadAllAttributes()
                                                       ->getSortedAttributes($setId);
        
        foreach ($attributes as $attribute) {
            if ($attribute->getId()
                && $attribute->isInSet($setId)
                && $attribute->getIsGlobal()
                && $attribute->getIsUserDefined()) {
                if ($attribute['frontend_input']=='select') {
                    $attributeId = $attribute->getAttributeId();
                    $response = $attributeModel->syncAttribute($attributeId);
                    if (isset($response['error']) && $response['error']) {
                        $fails .= $attributeId.'('.$response['error'].')';
                    }
                    if (isset($response['optcount']) && $response['optcount']) {
                        $optcount += $response['optcount'];
                    }
                    if (isset($response['odoo_attribute_id']) && $response['odoo_attribute_id']) {
                        $attr ++;
                        array_push($attributeArray, $response['odoo_attribute_id']);
                    }
                }
            }
        }
        $attributeModel->syncAttributeSets($setName, $setId, $attributeArray);

        return ['success'=>$attr, 'options'=>$optcount, 'failure'=>$fails];
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_set', 'entity_id');
    }
}
