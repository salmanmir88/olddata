<?php
/**
 * Webkul Odoomagentoconnect Connection Helper
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Helper;

if (!defined('xmlrpc.inc')) {
    require 'xmlrpc.inc';
    define('xmlrpc.inc', 1);
}
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;
use Webkul\Odoomagentoconnect\Helper\Data;

/**
 * Class Webkul Odoomagentoconnect Connection Helper
 */
class Connection extends \Magento\Search\Helper\Data
{
    protected $_scopeConfig;

    public static $odooUrl;
    public static $odooPort;
    public static $odooDb;
    public static $odooUser;
    public static $odooPwd;
    public static $mageUser;
    protected $_odoomagentoconnectData;
    protected $_errorMessage;
    protected $_session;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session $session,
        Data $odoomagentoconnectData
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        $this->_odoomagentoconnectData = $odoomagentoconnectData;

        self::$odooUrl = $scopeConfig->getValue('odoomagentoconnect/settings/odoo_url');

        self::$odooPort = $scopeConfig->getValue('odoomagentoconnect/settings/odoo_port');
        self::$odooDb = $scopeConfig->getValue('odoomagentoconnect/settings/odoo_database');
        self::$odooUser = $scopeConfig->getValue('odoomagentoconnect/settings/odoo_user');
        self::$odooPwd = $scopeConfig->getValue('odoomagentoconnect/settings/odoo_pwd');
        self::$mageUser = $this->getCurrentUser();
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function getSocketConnect()
    {
        if (!(self::$odooUrl && self::$odooDb && self::$odooUser && self::$odooPwd)) {
            $userId = 0;
            $this->getSession()->setUserId($userId);
            $errorMessage = "Sorry, Unable to find odoo configuration, Please fill the odoo details in configuration.";
            $this->getSession()->setErrorMessage($errorMessage);
            return false;
        }
        $userId = $this->getSession()->getUserId();
        $isSesson = $this->checkConfigurationChange();
        $errorMessage = "";
        if (!$isSesson || !$userId) {
            $sock = $this->getSocket();
            $msg = new xmlrpcmsg('login');
            $msg->addParam(new xmlrpcval(self::$odooDb, "string"));
            $msg->addParam(new xmlrpcval(self::$odooUser, "string"));
            $msg->addParam(new xmlrpcval(self::$odooPwd, "string"));
            $resp =  $sock->send($msg);
            if ($resp->faultCode()) {
                $error = $resp->faultString();
                $errorMessage = 'Failed to connect Odoo due to '.$error;
            } else {
                $val = $resp->value();
                $id = $val->scalarval();
                if ($id > 0) {
                    $userId = $id;
                } else {
                    $errorMessage ='Invalid Odoo login details.';
                    $userId = 0;
                }
            }
        }
        $this->getSession()->setUserId($userId);
        if ($userId) {
            $odooConfig = $this->checkOdooActiveConfig();
            if (!$odooConfig) {
                $userId = 0;
                $this->getSession()->setUserId($userId);
                $errorMessage = "Sorry, unable to find active Magento connection at odoo end.";
            } else {
                $errorMessage = "Congratulation, Magento is successfully connected with Odoo.";
            }
        }
        $this->getSession()->setErrorMessage($errorMessage);
    }


    public function checkOdooActiveConfig()
    {

        $userId = $this->getSession()->getUserId();
        if ($userId > 0) {
            $mageUrl = $this->_storeManager->getStore()->getBaseUrl();
            $client = $this->getClientConnect();
            $fields = ['magento_url'=>new xmlrpcval($mageUrl, "string"),];
            $msg2 = new xmlrpcmsg('execute');
            $msg2->addParam(new xmlrpcval(self::$odooDb, "string"));
            $msg2->addParam(new xmlrpcval($userId, "int"));
            $msg2->addParam(new xmlrpcval(self::$odooPwd, "string"));
            $msg2->addParam(new xmlrpcval("connector.instance", "string"));
            $msg2->addParam(new xmlrpcval("fetch_connection_info", "string"));
            $msg2->addParam(new xmlrpcval($fields, "struct"));
            $resp = $client->send($msg2);

            if ($resp->faultcode()) {
                $error = "Fetch Odoo Config Error,".$resp->faultString();
                $this->addError($error);
            } else {
                $data = $resp->value()->scalarval();
                if ($data) {
                    $erpCateg = $data['category']->me['array'][0]->me['int'];
                    $erpLang = $data['language']->me['string'];
                    $erpWarehouse = $data['warehouse_id']->me['array'][0]->me['int'];
                    $erpInstance = $data['id']->me['int'];
                    $this->_odoomagentoconnectData->setToSession($erpCateg, $erpLang, $erpWarehouse, $erpInstance);
                    return true;
                }
            }
        }
        return false;
    }

    public function getClientConnect()
    {
        if (!self::$odooUrl) {
            return false;
        }
        $client = new xmlrpc_client(self::$odooUrl.":".self::$odooPort."/xmlrpc/object");
        $client->setSSLVerifyPeer(0);
        $client->setSSLVerifyHost(0);
        return $client;
    }

    public function getSocket()
    {
        if (!self::$odooUrl) {
            return false;
        }
        $socket = new xmlrpc_client(self::$odooUrl.":".self::$odooPort."/xmlrpc/common");
        $socket->setSSLVerifyPeer(0);
        $socket->setSSLVerifyHost(0);
        return $socket;
    }

    public function setOdooConfigurationInSession()
    {
        $this->getSession()->setOdooUrl(self::$odooUrl);
        $this->getSession()->setOdooPort(self::$odooPort);
        $this->getSession()->setOdooDb(self::$odooDb);
        $this->getSession()->setOdooUser(self::$odooUser);
        $this->getSession()->setOdooPwd(self::$odooPwd);
    }

    public function checkConfigurationChange()
    {
        $flag = true;
        $odooUrl = $this->getSession()->getOdooUrl();
        if (self::$odooUrl != $odooUrl) {
            $flag = false;
        }
        $odooPort = $this->getSession()->getOdooPort();
        if (self::$odooPort != $odooPort) {
            $flag = false;
        }
        $odooDb = $this->getSession()->getOdooDb();
        if (self::$odooDb != $odooDb) {
            $flag = false;
        }
        $odooUser = $this->getSession()->getOdooUser();
        if (self::$odooUser != $odooUser) {
            $flag = false;
        }
        $odooPwd = $this->getSession()->getOdooPwd();
        if (self::$odooPwd != $odooPwd) {
            $flag = false;
        }
        if (!$flag) {
            $this->setOdooConfigurationInSession();
        }
        return $flag;
    }

    public function getOdooContext()
    {
        $defaultLang = $this->getSession()->getOdooLang();
        $defaultInstance = $this->getSession()->getOdooInstance();
        $context = [
                "lang"=>new xmlrpcval($defaultLang, "string"),
                "magento2"=>new xmlrpcval("magento2", "string"),
                "instance_id"=>new xmlrpcval($defaultInstance, "int"),
                ];
        return $context;
    }

    public function getCurrentUser()
    {
        $username = 'Magento';
        $user = $this->getSession()->getUser();
        if ($user) {
            $username = $username."-".$user->getUsername();
        } else {
            $username = $username.'-Front';
        }
        return $username;
    }

    public function addError($data, $file_name = 'odoo_connector.log')
    {
        
    }

    public function getStoreConfig($key)
    {
        return $this->_scopeConfig->getValue($key);
    }

    public function getModel($modelName)
    {
        return $this->_objectManager->get($modelName);
    }

    public function createMapping($modelName, $data)
    {
        if (!isset($data['created_by'])) {
            $data['created_by'] = $this->getCurrentUser();
        }
        $modelObj = $this->_objectManager->get($modelName);
        try {
            $modelObj->setData($data)->save();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function callOdooMethod($class, $method, $parameters, $needContext = false)
    {
        $this->getSocketConnect();
        $client = $this->getClientConnect();
        $userId = $this->getSession()->getUserId();
        $message = "API Call >> Class: $class -- Method: $method ";
        // $this->addError($message, 'odoo_connector.log');
        // $this->addError($parameters, 'odoo_connector.log');
        // $this->addError($needContext, 'odoo_connector.log');
        $response = [0, 'Error'];
        if ($userId > 0) {
            $msg = new xmlrpcmsg('execute_kw');
            $msg->addParam(new xmlrpcval(self::$odooDb, "string"));
            $msg->addParam(new xmlrpcval($userId, "int"));
            $msg->addParam(new xmlrpcval(self::$odooPwd, "string"));
            $msg->addParam(new xmlrpcval($class, "string"));
            $msg->addParam(new xmlrpcval($method, "string"));
            $msg->addParam(php_xmlrpc_encode($parameters));
            if ($needContext) {
                $context = $this->getOdooContext();
                if (is_array($needContext) == "array") {
                    $context = $needContext;
                }
                // $this->addError($context, 'odoo_connector_api.log');
                $this->addError($context, 'odoo_connector.log');
                $msg->addParam(php_xmlrpc_encode(['context' => $context]));
            }
            $resp = $client->send($msg);
            if ($resp->faultcode()) {
                $message = "API Call Error >> Class: $class -- Method: $method: ";
                $this->addError($message, 'odoo_connector_api.log');
                $this->addError($resp->faultString(), 'odoo_connector_api.log');
                $this->addError($resp->faultcode(), 'odoo_connector_api.log');
                $response = [0, $resp->faultString()];
            } else {
                $respVal = php_xmlrpc_decode($resp->value());
                // $message = "API Call Response >> ";
                // $this->addError($message, 'odoo_connector_api.log');
                // $this->addError($respVal, 'odoo_connector_api.log');
                $response = [1, $respVal];
            }
        } else {
            $errorMessage = $this->getSession()->getErrorMessage();
            // $message = "API Call Error >> Class: $class -- Method: $method -- Message: ".$errorMessage;
            // $this->addError($message, 'odoo_connector_api.log');
            $response = [0, $errorMessage];
        }

        return $response;
    }
    
}
