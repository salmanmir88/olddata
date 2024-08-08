<?php
/**
 * Webkul Odoomagentoconnect Currency ResourceModel
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Customer ResourceModel Class
 */
class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        \Magento\Customer\Model\Customer $customerObj,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_eventManager = $eventManager;
        $this->_customerObj = $customerObj;
        $this->_connection = $connection;
    }

    public function exportSpecificCustomer($customerId)
    {
        $response = [];
        if ($customerId) {
            $customer = $this->_customerObj->load($customerId);
            if (!$customer->getName()) {
                $response['odoo_id'] = 0;
                return $response;
            }
            $mageAddressId = 'customer';
            $mageCustomerId = $customer->getId();
            $storeId = $customer->getStoreId();
            $customerArray = $this->getCustomerArray($customer);
            $odooCustomerId = $this->odooCustomerCreate($customerArray, $mageCustomerId, $mageAddressId, $storeId);
            $response['odoo_id'] = $odooCustomerId;
            if ($odooCustomerId) {
                foreach ($customer->getAddresses() as $address) {
                    $odooAddressId = $this->syncSpecificAddressAtOdoo($odooCustomerId, $customer, $address);
                }
            }
        }
        return $response;
    }

    public function updateSpecificCustomer($mappingId, $customerId, $odooCustomerId)
    {
        $helper = $this->_connection;
        if ($customerId && $odooCustomerId) {
            $model = $helper->getModel(\Webkul\Odoomagentoconnect\Model\Customer::class);
            $autoSync = $helper->getStoreConfig('odoomagentoconnect/automatization_settings/auto_customer');
            $customer = $this->_customerObj->load($customerId);
            $mageAddressId = 'customer';
            $mageCustomerId = $customer->getId();
            $customerArray = $this->getCustomerArray($customer);
            $response = $this->odooCustomerUpdate($customerId, $mageAddressId, $customerArray, $odooCustomerId);

            /*  Address Synchronization  */
            foreach ($customer->getAddresses() as $address) {
                $addresscollection = $model->getCollection()
                                    ->addFieldToFilter('magento_id', ['eq'=>$customerId])
                                    ->addFieldToFilter('address_id', ['eq'=>$address->getId()]);
                if ($addresscollection->getSize() == 0) {
                    $odooAddressId = $this->syncSpecificAddressAtOdoo($odooCustomerId, $customer, $address);
                }
            }
            return true;
        }
    }
    
    public function syncSpecificAddressAtOdoo($odooCustomerId, $customer, $address, $method = 'create', $partnerId = 0)
    {
        $odooId = 0;
        $addressArray = [];
        $streets = $address->getStreet();
        if (count($streets)>1) {
            $street = urlencode($streets[0]);
            $street2 = urlencode($streets[1]);
        } else {
            $street = urlencode($streets[0]);
            $street2 = urlencode('');
        }
        $customerId = $customer->getId();
        $storeId = $customer->getStoreId();
        $addressId = $address->getId();
        $type = $this->getAddressType($customer, $address);
        $addressArray =  [
                    'name'=>urlencode($address->getName()),
                    'street'=>$street,
                    'street2'=>$street2,
                    'city'=>urlencode($address->getCity()),
                    'email'=>urlencode($customer->getEmail()),
                    'zip'=>$address->getPostcode(),
                    'phone'=>$address->getTelephone(),
                    'country_code'=>$address->getCountryId(),
                    'region'=>urlencode($address->getRegion()),
                    'type'=>$type,
                    'wk_company'=>urlencode($address->getCompany()),
                    'customer_rank'=>false,
                    'parent_id'=>(int)$odooCustomerId,
                ];
        if ($method == 'create') {
            $odooId = $this->odooCustomerCreate($addressArray, $customerId, $addressId, $storeId);
        } elseif ($method == 'write') {
            $this->odooCustomerUpdate($customerId, $addressId, $addressArray, $partnerId);
        }
        /* Customer Vat Synchronization*/
        $taxVat = $customer->getTaxvat();
        if ($taxVat) {
            preg_match('/^[a-zA-Z]{2}/', $taxVat, $matches);
            if (!$matches) {
                $taxVat = $address->getCountryId().''.$taxVat;
            }
            $vatArray =  [
                    'vat'=>$taxVat,
                ];
            $this->odooCustomerUpdate($customerId, $addressId, $vatArray, $odooCustomerId);
        }
        return $odooId;
    }

    public function getCustomerArray($customer)
    {
        $customerArray =  [
            'name'=>urlencode($customer->getName()),
            'email'=>urlencode($customer->getEmail()),
            'is_company'=>false,
        ];
        return $customerArray;
    }

    public function odooCustomerCreate($customerArray, $mageCustomerId, $mageAddressId, $storeId = 0)
    {
        $odooId = 0;
        $extraFieldArray = [];
        $helper = $this->_connection;
        $context = $helper->getOdooContext();
        $instanceId = $context['instance_id'];
        /* Adding Extra Fields*/
        $helper->getSession()->setExtraFieldArray($extraFieldArray);
        $this->_eventManager->dispatch('customer_sync_before', ['mage_id' => $storeId]);
        $extraFieldArray = $helper->getSession()->getExtraFieldArray();
        foreach ($extraFieldArray as $field => $value) {
            $customerArray[$field]= $value;
        }
        $resp = $helper->callOdooMethod('res.partner', 'create', [$customerArray], true);
        if ($resp && $resp[0]) {
            $odooId = $resp[1];
            if ($odooId && $mageCustomerId && $mageAddressId) {
                $mappingData = [
                    'odoo_id'=>$odooId,
                    'magento_id'=>$mageCustomerId,
                    'address_id'=>$mageAddressId,
                    'created_by'=>$helper::$mageUser
                ];
                $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Customer::class, $mappingData);
                $mapArray = [
                    'name'=>$odooId,
                    'odoo_id'=>$odooId,
                    'ecomm_id'=>$mageCustomerId,
                    'ecomm_address_id'=>$mageAddressId,
                    'created_by'=>$helper::$mageUser,
                    'instance_id'=>$instanceId,
                ];
                $resp = $helper->callOdooMethod('connector.partner.mapping', 'create', [$mapArray], true);
            }
        } else {
            $respMessage = $resp[1];
            $error = "Export Error, Customer Id ".$mageCustomerId."(".$mageAddressId.") >>".$respMessage;
            $helper->addError($error);
        }
        return $odooId;
    }

    public function odooCustomerUpdate($customerId, $addressId, $addressArray, $odooCustomerId)
    {

        $helper = $this->_connection;
        $response = false;

        $resp = $helper->callOdooMethod('res.partner', 'write', [$odooCustomerId, $addressArray], true);
        if ($resp && $resp[0]) {
            $response = true;
        } else {
            $respMessage = $resp[1];
            $error = "Customer Update Error, Customer Id ".$customerId."(".$addressId.") >>".$respMessage;
            $helper->addError($error);
        }
        return $response;
    }

    public function getAddressType($customer, $address)
    {
        $type = '';

        if ($customer->getDefaultBilling() && $customer->getDefaultBilling() == $address->getId()) {
            $type = 'invoice';
        } elseif ($customer->getDefaultShipping() && $customer->getDefaultShipping() == $address->getId()) {
            $type = 'delivery';
        } else {
            $type = 'other';
        }

        return $type;
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_customer', 'entity_id');
    }
}
