<?php
/**
 * Webkul Odoomagentoconnect Option ResourceModel
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Option ResourceModel Class
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection,
        \Magento\Catalog\Model\Product $productModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        $resourcePrefix = null
    ) {
        $this->_attributeCollection = $attributeCollection;
        $this->_productModel = $productModel;
        $this->_connection = $connection;
        parent::__construct($context, $resourcePrefix);
    }

    public function getMageOptionArray()
    {
        $attributeOption = [];
        $attributeOption[''] ='--Select Magento Attribute Option--';
        $collection = $this->_attributeCollection->addVisibleFilter();
        $collection->getSelect()->where('is_user_defined = 1');
        $collection->getSelect()->where('is_global = 1');
        $collection->getSelect()->where("frontend_input = 'select'");
        foreach ($collection as $value) {
            $attributeName = $value->getAttributeCode();
            $options = $value->getSource()->getAllOptions(false);
            foreach ($options as $key) {
                $mageOptionId = $key['value'];
                $mageOptionLabel = $key['label'];
                $attributeOption[$mageOptionId] = "$attributeName: $mageOptionLabel";
            }
        }
        return $attributeOption;
    }

    public function getOdooOptionArray()
    {
        $optionArray = [];
        $helper = $this->_connection;
        $resp = $helper->callOdooMethod('product.attribute.value', 'search_read', [[],['id', 'display_name']]);
        if ($resp && $resp[0]) {
            $optionArray[''] ='--Select Odoo Attribute Option--';
            $odooOptions = $resp[1];
            foreach ($odooOptions as $odooOption) {
                $optionArray[$odooOption['id']] = $odooOption['display_name'];
            }
        } else {
            $optionArray['error'] = $resp[1];
        }
        return $optionArray;
    }

    public function syncAllAttributeOptions($attributeId, $odooAttributeId)
    {
        $count = 0;
        $helper = $this->_connection;
        $collection = $this->_productModel->getResource()->getAttribute($attributeId);
        $options = $collection->getSource()->getAllOptions(false);
        foreach ($options as $key) {
            $label = $key['label'];
            $optionId = $key['value'];
            $mappingcollection =  $helper->getModel(\Webkul\Odoomagentoconnect\Model\Option::class)
                ->getCollection()
                ->addFieldToFilter('magento_id', $optionId);
            if ($mappingcollection->getSize() == 0) {
                $optionArray = [
                    'name'=>$label,
                    'attribute_id'=>$odooAttributeId,
                ];
                $resp = $helper->callOdooMethod('product.attribute.value', 'create', [$optionArray]);
                if ($resp && $resp[0]) {
                    $odooOptionId = $resp[1];
                    $mappingData = [
                        'name'=>$label,
                        'odoo_id'=>$odooOptionId,
                        'magento_id'=>$optionId,
                        'created_by'=>$helper::$mageUser
                    ];
                    $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Option::class, $mappingData);
                    $this->mapAttributeOption($mappingData);
                    $count = ++$count;
                } else {
                    $respMessage = $resp[1];
                    $error = "Export Error, Attribute Id ".$attributeId.", Option Id ".$optionId." >> ".$respMessage;
                    $helper->addError($error);
                }
            }
        }
        return $count;
    }

    public function mapAttributeOption($data)
    {
        $mappingArray = [
            'name'=>$data['odoo_id'],
            'odoo_id'=>$data['odoo_id'],
            'ecomm_id'=>$data['magento_id'],
            'created_by'=>$data['created_by'],
        ];
        $resp = $this->_connection->callOdooMethod('connector.option.mapping', 'create', [$mappingArray], true);
        return true;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_option', 'entity_id');
    }
}
