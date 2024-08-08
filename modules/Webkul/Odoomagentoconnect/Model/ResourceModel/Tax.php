<?php
/**
 * Webkul Odoomagentoconnect Tax ResourceModel
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Tax ResourceModel Class
 */
class Tax extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Tax\Model\Calculation\Rate $rateModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        $resourcePrefix = null
    ) {
        $this->_connection = $connection;
        $this->_rateModel = $rateModel;
        parent::__construct($context, $resourcePrefix);
    }

    public function getMageTaxArray()
    {
        $Tax = [];
        $Tax[''] ='--Select Magento Tax--';
        $taxCollection = $this->_rateModel->getCollection()->getData();
        foreach ($taxCollection as $tax) {
            $mageTaxId = $tax['tax_calculation_rate_id'];
            $mageTaxCode = $tax["code"];
            $mageTaxRate = $tax["rate"];
            $t = $mageTaxCode.'('.$mageTaxRate.'%)';
            $Tax[$mageTaxId] = $t;
        }
        return $Tax;
    }

    public function getOdooTaxArray()
    {
        $Tax = [];
        $resp = $this->_connection->callOdooMethod('account.tax', 'search_read', [[],['id', 'name']]);
        if ($resp && $resp[0]) {
            $Tax[''] ='--Select Odoo Tax--';
            $odooTaxes = $resp[1];
            foreach ($odooTaxes as $odooTax) {
                $Tax[$odooTax['id']] = $odooTax['name'];
            }
        } else {
            $Tax['error'] = $resp[1];
        }
        return $Tax;
    }

    public function getTaxArray($taxId)
    {
        $includesTax = $this->_connection->getStoreConfig('tax/calculation/price_includes_tax');
        $taxRate = $this->_rateModel->load($taxId);
        $taxArray = [
                'name'=>$taxRate->getCode(),
                'description'=>$taxRate->getCode(),
                'amount_type'=>'percent',
                'price_include'=>(boolean)$includesTax,
                'amount'=>$taxRate->getRate()
        ];
        return $taxArray;
    }

    public function createSpecificTax($mageId)
    {
        $response = [];
        $helper = $this->_connection;
        if ($mageId) {
            $taxArray = $this->getTaxArray($mageId);

            $taxCode = $this->_rateModel->load($mageId)->getCode();

            $resp = $helper->callOdooMethod('account.tax', 'create', [$taxArray]);
            if ($resp && $resp[0]) {
                $odooId = $resp[1];
                $mappingData = [
                    'odoo_id'=>$odooId,
                    'magento_id'=>$mageId,
                    'code'=>$taxCode,
                    'created_by'=>$helper::$mageUser
                ];
                $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Tax::class, $mappingData);
                $response = [
                    'success'=> true,
                    'odoo_id'=>$odooId,
                ];
            } else {
                $respMessage = $resp[1];
                $error = "Export error, tax id $mageId & code $taxCode >> ".$respMessage;
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

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_tax', 'entity_id');
    }
}
