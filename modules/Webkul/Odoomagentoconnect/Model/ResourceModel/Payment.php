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
 * Webkul Odoomagentoconnect Payment ResourceModel Class
 */
class Payment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_scopeConfig;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Config $paymentConfig,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        $resourcePrefix = null
    ) {
        $this->_paymentConfig = $paymentConfig;
        $this->_scopeConfig = $scopeConfig;
        $this->_connection = $connection;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_payment', 'entity_id');
    }

    public function getMagePaymentArray()
    {
        $payment = [];
        $payment[''] ='--Select Magento Payment Method--';
        $helper = $this->_connection;
        $collection = $this->_paymentConfig->getActiveMethods();
        foreach ($collection as $paymentCode => $paymentModel) {
            $paymentTitle = $helper->getStoreConfig('payment/'.$paymentCode.'/title');
            $payment[$paymentCode] = $paymentTitle;
        }
        return $payment;
    }

    public function getOdooPaymentArray()
    {
        $paymentData = [];
        $helper = $this->_connection;
        $params = [
            [['type', 'in', ['bank', 'cash']]], // Domain
            ['id', 'name'] // Fields
        ];
        $resp = $helper->callOdooMethod('account.journal', 'search_read', $params);
        if ($resp && $resp[0]) {
            $paymentData[''] ='--Select Odoo Payment--';
            $odooJournals = $resp[1];
            foreach ($odooJournals as $odooJournal) {
                $paymentData[$odooJournal['id']] = $odooJournal['name'];
            }
        } else {
            $paymentData['error'] = $resp[1];
        }
        return $paymentData;
    }

    public function syncSpecificPayment($paymentMethod)
    {
        $response = ['odoo_id'=>0];
        $helper = $this->_connection;
        if ($paymentMethod) {
            $paymentArray = [
                'name'=>$paymentMethod,
                'type'=>'cash'
            ];
            $resp = $helper->callOdooMethod('connector.snippet', 'create_payment_method', [$paymentArray], true);
            $helper->addError($resp, 'odoo_connector.log');
            if ($resp && $resp[0]) {
                $odooId = $resp[1]['odoo_id'];
                $mappingData = [
                    'magento_id'=>$paymentMethod,
                    'odoo_id'=>$odooId,
                    'created_by'=>$helper::$mageUser
                ];
                $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Payment::class, $mappingData);
                $response = [
                    'success'=> true,
                    'odoo_id'=>$odooId,
                ];
            } else {
                $respMessage = $resp[1];
                $error = "Export error, Payment Method ".$paymentMethod." >> ".$respMessage;
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
