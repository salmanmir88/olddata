<?php
/**
 * Webkul Odoomagentoconnect Currency ResourceModel
 *
 * @author    Webkul
 * @api
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Currency ResourceModel Class
 */
class Currency extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        \Magento\Directory\Model\Currency $currencyModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        $resourcePrefix = null
    ) {
    
        parent::__construct($context, $resourcePrefix);
        $this->_currencyModel = $currencyModel;
        $this->_connection = $connection;
    }

    public function getMageCurrencyArray()
    {
        $Currency = [];
        $Currency[''] ='--Select Magento Currency--';
        $collection = $this->_currencyModel->getConfigAllowCurrencies();
        foreach ($collection as $currency) {
            $Currency[$currency] = $currency;
        }
        
        return $Currency;
    }

    public function getOdooCurrencyArray()
    {
        $currencyArray = [];
        $helper = $this->_connection;
        $resp = $helper->callOdooMethod('product.pricelist', 'search_read', [[],['id', 'display_name']]);
        if ($resp && $resp[0]) {
            $currencyArray[''] ='--Select Odoo Attribute Option--';
            $odooCurrencies = $resp[1];
            foreach ($odooCurrencies as $odooCurrency) {
                $currencyArray[$odooCurrency['id']] = $odooCurrency['display_name'];
            }
        } else {
            $currencyArray['error'] = $resp[1];
        }
        return $currencyArray;
    }

    public function syncCurrency($currencyCode)
    {
        $pricelistId = 0;
        $helper = $this->_connection;
        if ($currencyCode) {
            $mappingcollection = $helper->getModel(\Webkul\Odoomagentoconnect\Model\Currency::class)
                ->getCollection()
                ->addFieldToFilter('magento_id', ['eq'=>$currencyCode]);
            if ($mappingcollection->getSize() > 0) {
                foreach ($mappingcollection as $map) {
                    $pricelistId = (int)$map->getOdooId();
                }
            } else {
                $pricelistArray =  [
                    'code'=>$currencyCode
                ];
                $resp = $helper->callOdooMethod('connector.snippet', 'create_pricelist', [$pricelistArray], true);
                if ($resp && $resp[0]) {
                    $pricelistId = $resp[1];
                    $mappingData = [
                        'odoo_id'=>$pricelistId,
                        'magento_id'=>$currencyCode,
                        'created_by'=>$helper::$mageUser
                    ];
                    $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Currency::class, $mappingData);
                } else {
                    $respMessage = $resp[1];
                    $error = "Export error, currency code "
                    .$currencyCode." during currency create or Please enable this currency at odoo end >> ".$respMessage;
                    $helper->addError($error);

                }
            }
        }
        return $pricelistId;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_currency', 'entity_id');
    }
}
