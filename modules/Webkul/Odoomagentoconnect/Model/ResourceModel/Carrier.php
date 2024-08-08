<?php
/**
 * Webkul Odoomagentoconnect Carrier ResourceModel
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Carrier ResourceModel Class
 */
class Carrier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Shipping\Model\Config $shippingConfigModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_shippingConfigModel = $shippingConfigModel;
        $this->_connection = $connection;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_carrier', 'entity_id');
    }

    public function getMageCarrierArray()
    {
        $carrier = [];
        $carrier[''] ='--Select Magento Carrier--';
        $helper = $this->_connection;
        $collection = $this->_shippingConfigModel->getActiveCarriers();

        foreach ($collection as $shippingCode => $shippingModel) {
            $shippingTitle = $helper->getStoreConfig('carriers/'.$shippingCode.'/title');
            if (!$shippingTitle) {
                $shippingTitle = $shippingCode;
            }
            $carrier[$shippingCode] = $shippingTitle;
        }
        
        return $carrier;
    }

    public function getOdooCarrierArray()
    {
        $carrierArray = [];
        $helper = $this->_connection;
        $resp = $helper->callOdooMethod('delivery.carrier', 'search_read', [[],['id', 'name']]);
        if ($resp && $resp[0]) {
            $carrierArray[''] ='--Select Odoo Attribute Option--';
            $odoocarriers = $resp[1];
            foreach ($odoocarriers as $odooCarrier) {
                $carrierArray[$odooCarrier['id']] = $odooCarrier['name'];
            }
        } else {
            $carrierArray['error'] = $resp[1];
        }
        return $carrierArray;
    }

    public function checkSpecificCarrier($shippingCode)
    {
        $odooId = 0;
        $carrierProductId = 0;
        $collection =  $this->_connection->getModel(\Webkul\Odoomagentoconnect\Model\Carrier::class)
            ->getCollection()
            ->addFieldToFilter('carrier_code', ['eq'=>$shippingCode]);
        if ($collection->getSize() > 0) {
            foreach ($collection as $check) {
                $odooId = (int)$check->getOdooId();
                $carrierProductId = (int)$check->getCarrierProductId();
            }
        } else {
            $response = $this->createCarrierAtOdoo($shippingCode);
            if ($response['odoo_id'] > 0) {
                $odooId = $response['odoo_id'];
            }
        }
        return [$odooId];
    }

    public function createCarrierAtOdoo($shippingCode)
    {
        $response = [];
        $helper = $this->_connection;
        if ($shippingCode) {
            $shippingTitle = $helper->getStoreConfig('carriers/'.$shippingCode.'/title');
            if (!$shippingTitle) {
                $shippingTitle = $shippingCode;
            }
            $carrierArray = [
                'name'=>$shippingTitle
            ];
            $resp = $helper->callOdooMethod('delivery.carrier', 'create', [$carrierArray], true);
            if ($resp && $resp[0]) {
                $odooId = $resp[1];
                $mappingData = [
                    'carrier_code'=>$shippingCode,
                    'carrier_name'=>$shippingTitle,
                    'odoo_id'=>$odooId,
                    'created_by'=>$helper::$mageUser
                ];
                $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Carrier::class, $mappingData);
                $response = [
                    'odoo_id'=>$odooId,
                    'success'=>true
                ];
            } else {
                $respMessage = $resp[1];
                $error = "Export error, carrier code ".$shippingCode." >> ".$respMessage;
                $helper->addError($error);
                $response = [
                    'odoo_id'=>0,
                    'success'=> false,
                    'message'=>$error
                ];
            }
        }
        return $response;
    }
}
