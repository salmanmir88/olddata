<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */

namespace Apptrian\FacebookPixel\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    public $moduleList;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    public $httpHeader;
    
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    public $remoteAddress;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;
    
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    public $cookieManager;
    
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;
    
    /**
     * Redirect interface
     *
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    public $redirect;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    
    /**
     * @var \Apptrian\FacebookPixel\Service\CurrentCustomer
     */
    public $currentCustomer;
    
    /**
     * @var \Apptrian\FacebookPixel\Service\CurrentCategory
     */
    public $currentCategory;
    
    /**
     * @var \Apptrian\FacebookPixel\Service\CurrentProduct
     */
    public $currentProduct;
    
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    public $catalogHelper;
    
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    public $categoryFactory;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;
    
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    public $configurableProductModel;
    
    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    public $bundleProductModel;
    
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    public $groupedProductModel;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;
    
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    public $addressFactory;
    
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    public $regionFactory;
    
    /**
     * Tax config model
     *
     * @var \Magento\Tax\Model\Config
     */
    public $taxConfig;
    
    /**
     * Tax display flag property
     *
     * @var null|int
     */
    public $taxDisplayFlag = null;
    
    /**
     * Tax catalog flag property
     *
     * @var null|int
     */
    public $taxCatalogFlag = null;
    
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    public $localeFormat;
    
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    public $localeResolver;
    
    /**
     * Store object
     *
     * @var null|\Magento\Store\Model\Store
     */
    public $store = null;
    
    /**
     * Store ID property
     *
     * @var null|int
     */
    public $storeId = null;
    
    /**
     * Base currency code property
     *
     * @var null|string
     */
    public $baseCurrencyCode = null;
    
    /**
     * Current currency code property
     *
     * @var null|string
     */
    public $currentCurrencyCode = null;
    
    /**
     * Category ID property
     *
     * @var int
     */
    public $categoryId = 0;
    
    /**
     * Category event name property
     *
     * @var null|string
     */
    public $categoryEventName = null;
    
    /**
     * Search event name property
     *
     * @var null|string
     */
    public $searchEventName = null;
    
    /**
     * Parameter name for search event
     *
     * @var null|string
     */
    public $searchParamName = null;
    
    /**
     * Product type property
     *
     * @var string
     */
    public $productType = null;
    
    /**
     * Product ID property
     *
     * @var string|integer
     */
    public $productId = 0;
    
    /**
     * Product children (contents array with IDs)
     *
     * @var array
     */
    public $contentsWithIds = [];
    
    /**
     * Bundle product options to product IDs map
     *
     * @var array
     */
    public $bundleProductOptionsData = [];
    
    /**
     * Configurable product options to product IDs map
     *
     * @var array
     */
    public $configurableProductOptionsData = [];
    
    /**
     * Configurable product allowed products array
     *
     * @var array
     */
    public $allowedProducts = [];
    
    /**
     * Data with content_ids instead of contents
     *
     * @var array
     */
    public $dataWithContentIds = [];
    
    /**
     * User Data property
     *
     * @var array
     */
    public $userData = [];
    
    /**
     * Facebook product identifier. Magneto Product SKU or Magento Product ID.
     *
     * @var null|string
     */
    public $ident = null;
    
    /**
     * Advanced Matching allowed data keys.
     *
     * @var array
     */
    public $allowedMatchingKeys = [
        'em',
        'ph',
        'fn',
        'ln',
        'ge',
        'db',
        'ct',
        'st',
        'zp',
        'country',
        'external_id'
    ];
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Apptrian\FacebookPixel\Service\CurrentCustomer $currentCustomer
     * @param \Apptrian\FacebookPixel\Service\CurrentCategory $currentCategory
     * @param \Apptrian\FacebookPixel\Service\CurrentProduct $currentProduct
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $cP
     * @param \Magento\Bundle\Model\Product\Type $bundleProduct
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Apptrian\FacebookPixel\Service\CurrentCustomer $currentCustomer,
        \Apptrian\FacebookPixel\Service\CurrentCategory $currentCategory,
        \Apptrian\FacebookPixel\Service\CurrentProduct $currentProduct,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $cP,
        \Magento\Bundle\Model\Product\Type $bundleProduct,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->moduleList               = $moduleList;
        $this->scopeConfig              = $context->getScopeConfig();
        $this->httpHeader               = $context->getHttpHeader();
        $this->remoteAddress            = $context->getRemoteAddress();
        $this->request                  = $context->getRequest();
        $this->logger                   = $context->getLogger();
        $this->curl                     = $curl;
        $this->cookieManager            = $cookieManager;
        $this->redirect                 = $redirect;
        $this->storeManager             = $storeManager;
        $this->currentCustomer          = $currentCustomer;
        $this->currentCategory          = $currentCategory;
        $this->currentProduct           = $currentProduct;
        $this->catalogHelper            = $catalogHelper;
        $this->categoryFactory          = $categoryFactory;
        $this->productFactory           = $productFactory;
        $this->configurableProductModel = $cP;
        $this->bundleProductModel       = $bundleProduct;
        $this->groupedProductModel      = $groupedProduct;
        $this->checkoutSession          = $checkoutSession;
        $this->customerFactory          = $customerFactory;
        $this->addressFactory           = $addressFactory;
        $this->regionFactory            = $regionFactory;
        $this->taxConfig                = $taxConfig;
        $this->localeFormat             = $localeFormat;
        $this->localeResolver           = $localeResolver;
        
        parent::__construct($context);
    }
    
    /**
     * Returns extension version.
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        $moduleCode = 'Apptrian_FacebookPixel';
        $moduleInfo = $this->moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }
    
    /**
     * Returns Facebook Pixel ID from config.
     * It might return multiple IDs so there is option
     * to convert it to array.
     *
     * @param int|bool $toArray
     * @return array
     */
    public function getFacebookPixelId($toArray = true)
    {
        $id = '';
        
        $id = $this->getConfig(
            'apptrian_facebookpixel/client/pixel_id',
            $this->getStoreId()
        );
        
        if ($toArray) {
            return explode(',', (string) $id);
        } else {
            return $id;
        }
    }
    
    /**
     * Returns access token for Facebook Server-Side API.
     *
     * @param int|bool $toArray
     * @return string
     */
    public function getAccessToken($toArray = true)
    {
        $at = '';
        
        $at = $this->getConfig(
            'apptrian_facebookpixel/server/access_token',
            $this->getStoreId()
        );
        
        if ($toArray) {
            return explode(',', (string) $at);
        } else {
            return $at;
        }
    }
    
    /**
     * Returns Facebook Server-Side API version.
     *
     * @return string
     */
    public function getApiVersion()
    {
        return $this->getConfig(
            'apptrian_facebookpixel/server/api_version',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns test event code for Facebook Server-Side API.
     *
     * @return string
     */
    public function getTestEventCode()
    {
        return $this->getConfig(
            'apptrian_facebookpixel/server/test_event_code',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns array of handles based on configuration.
     *
     * @param string $type
     * @return array
     */
    public function getPageHandles($type = 'client')
    {
        $handles = [];
        
        $config = $this->getConfig(
            'apptrian_facebookpixel/' . $type . '/page_handles',
            $this->getStoreId()
        );
        
        $handles = explode(',', (string) $config);
        
        return $handles;
    }
    
    /**
     * Returns customer data for Client-Side advanced matching.
     *
     * @param int $customerId
     * @return array
     */
    public function getUserDataForClient($customerId = 0)
    {
        $data = [];
        
        if ($customerId) {
            $userData = $this->getUserData($customerId);
        } else {
            $userData = $this->getUserDataFromOrder();
        }
        
        $map = $this->getMatchingParamsMap();
        $paramName = '';
        
        foreach ($userData as $key => $value) {
            if ($value && array_key_exists($key, $map)) {
                $paramName = $map[$key];
                
                // For Client-Side API data must not be encrypted
                $data[$paramName] = $this->formatData($value);
            }
        }
        
        return $data;
    }
    
    /**
     * Returns customer data for Server-Side advanced matching.
     *
     * @param int $customerId
     * @return array
     */
    public function getUserDataForServer($customerId = 0)
    {
        $data = [];
        
        $fbc = $this->getFbc();
        $fbp = $this->getFbp();
        
        $data['client_ip_address'] = $this->getClientIpAddress();
        $data['client_user_agent'] = $this->getClientUserAgent();
        
        if ($fbc) {
            $data['fbc'] = $fbc;
        }
        
        if ($fbp) {
            $data['fbp'] = $fbp;
        }
        
        // if there is no customer id try to get it from the service
        if (!$customerId) {
            $customerId = $this->currentCustomer->getCustomerId();
        }
        
        if ($customerId) {
            $userData = $this->getUserData($customerId);
        } else {
            $userData = $this->getUserDataFromOrder();
        }
        
        $map = $this->getMatchingParamsMap();
        $paramName = '';
        
        foreach ($userData as $key => $value) {
            if ($value && array_key_exists($key, $map)) {
                $paramName = $map[$key];
                
                // For Server-Side API data must be encrypted
                $data[$paramName] = $this->encryptData($this->formatData($value));
            }
        }
        
        return $data;
    }
    
    /**
     * Returns customer data needed for advanced matching.
     *
     * @param int $customerId
     * @return array
     */
    public function getUserData($customerId = 0)
    {
        if (!empty($this->userData)) {
            return $this->userData;
        }
        
        $data         = [];
        $address      = null;
        $addressId    = 0;
        $customer     = null;
        
        if (!$customerId) {
            return [];
        }
        
        $customer = $this->getCustomerById($customerId);
        
        if (null == $customer) {
            return [];
        }
        
        $data['external_id'] = $customerId;
        
        $data['db'] = $this->stripNonNumeric(
            $this->datetimeToDate($customer->getDob())
        );
        $data['em'] = $customer->getEmail();
        $data['fn'] = $customer->getFirstname();
        
        // 1 male, 2 female, 3 not specified
        $ge = $customer->getGender();
        
        if ($ge == 1) {
            $data['ge'] =  'm';
        }
        
        if ($ge == 2) {
            $data['ge'] =  'f';
        }
        
        $data['ln'] = $customer->getLastname();
        
        // Get billing address
        $addressId = $customer->getDefaultBilling();
        
        // If there is no billing address get shipping address
        if (!$addressId) {
            $addressId = $customer->getDefaultShipping();
        }
        
        if ($addressId) {
            $address = $this->getCustomerAddressById($addressId);
            
            $data['ct']      = $address->getCity();
            $data['country'] = $address->getCountry();
            $data['ph']      = $this->stripNonNumeric($address->getTelephone());
            $data['st']      = $this->getRegionCodeOrNameById(
                $address->getRegionId()
            );
            $data['zp']      = $address->getPostcode();
        }
        
        $this->userData = $data;
        return $data;
    }
    
    /**
     * Returns customer data needed for advanced matching from order object.
     *
     * @return array
     */
    public function getUserDataFromOrder()
    {
        if (!empty($this->userData)) {
            return $this->userData;
        }
        
        $data         = [];
        $address      = null;
        
        $orderId = $this->checkoutSession->getLastRealOrder()->getId();
        
        if ($orderId) {
            $order = $this->checkoutSession->getLastRealOrder();
            
            $customerId = $order->getCustomerId();
            
            if ($customerId) {
                return $this->getUserData($customerId);
            } else {
                $data['db'] = $this->stripNonNumeric(
                    $this->datetimeToDate($order->getCustomerDob())
                );
                $data['em'] = $order->getCustomerEmail();
                $data['fn'] = $order->getCustomerFirstname();
                
                // 1 male, 2 female, 3 not specified
                $ge = $order->getCustomerGender();
                
                if ($ge == 1) {
                    $data['ge'] =  'm';
                }
                
                if ($ge == 2) {
                    $data['ge'] =  'f';
                }
                
                $data['ln'] = $order->getCustomerLastname();
                
                // Get billing address
                $address = $order->getBillingAddress()->getData();
                
                // If there is no billing address get shipping address
                if (empty($address)) {
                    $address = $order->getShippingAddress()->getData();
                }
                
                if (!empty($address)) {
                    if (isset($address['city'])) {
                        $data['ct'] = $address['city'];
                    }
                    
                    if (isset($address['country_id'])) {
                        $data['country'] = $address['country_id'];
                    }
                    
                    if (isset($address['telephone'])) {
                        $data['ph'] = $this->stripNonNumeric($address['telephone']);
                    }
                    
                    if (isset($address['region_id'])) {
                        $data['st'] = $this->getRegionCodeOrNameById(
                            $address['region_id']
                        );
                    }
                    
                    if (isset($address['postcode'])) {
                        $data['zp'] = $address['postcode'];
                    }
                }
                
                $this->userData = $data;
                return $data;
            }
        }
        
        return $data;
    }
    
    /**
     * Returns category data for Client-Side tracking.
     *
     * @return array
     */
    public function getCategoryDataForClient()
    {
        $isEnabled = $this->isEventEnabled('ViewContent');
        
        if (!$isEnabled) {
            return [];
        }
        
        $d = [];
        
        $categoryEventName = $this->getCategoryEventName();
        
        if ($categoryEventName) {
            $data = $this->getCategoryData();
            
            if (null == $data) {
                $d['data'] = [];
            } else {
                $d['data'] = $data;
            }
            
            $d['event_name'] = $categoryEventName;
            $d['event_id']   = $this->generateEventId(
                $categoryEventName,
                $this->categoryId,
                'category',
                true
            );
        }
        
        return $d;
    }
    
    /**
     * Returns category data for Server-Side tracking.
     *
     * @param int $categoryId
     * @param int $customerId
     * @return array
     */
    public function getCategoryDataForServer($categoryId = 0, $customerId = 0)
    {
        $isEnabled = $this->isEventEnabled('ViewContent', true);
        
        $d = [];
        
        $i = 0;
        
        if ($isEnabled) {
            $categoryEventName = $this->getCategoryEventName();
            
            if ($categoryEventName) {
                $userData = $this->getUserDataForServer($customerId);
                $data     = $this->getCategoryData($categoryId);
                
                if (null == $data) {
                    $customData = [];
                } else {
                    $customData = $data;
                }
                
                $d['data'][0]['event_name'] = $categoryEventName;
                $d['data'][0]['event_time'] = time();
                $d['data'][0]['event_id']   = $this->generateEventId(
                    $categoryEventName,
                    $this->categoryId,
                    'category'
                );
                
                // Optional
                $d['data'][0]['event_source_url'] = $this->getCurrentUrl();
                
                $d['data'][0]['user_data']   = $userData;
                $d['data'][0]['custom_data'] = $customData;
                
                // Optional
                $d['data'][0]['opt_out'] = false;
                
                $i = 1;
            }
        }
        
        // Add PageView based on config
        $isPageViewEnabled = $this->isEventEnabled('PageView', true);
        $isPageViewWithAll = $this->isPageViewWithAll(true);
        
        if ($isPageViewEnabled && $isPageViewWithAll) {
            $pageViewEvent = $this->getDataForServerPageViewEvent($customerId);
            
            if ($i) {
                $pageViewData  = $pageViewEvent['data'][0];
                $d['data'][$i] = $pageViewData;
            } else {
                $d = $pageViewEvent;
            }
        }
        
        return $d;
    }
    
    /**
     * Returns category data needed for tracking.
     *
     * @param int $categoryId
     * @return array
     */
    public function getCategoryData($categoryId = 0)
    {
        if ($categoryId) {
            $c = $this->getCategory($categoryId);
        } else {
            $c = $this->getCategory();
        }
        
        if (null == $c) {
            return null;
        }
        
        $data = [];
        
        // Get category ID
        $this->categoryId = $c->getId();
        
        // Get event name
        $eventName = $this->getCategoryEventName();
        
        if ($eventName) {
            // Custom Parameters
            $attributeValue = '';
            $map = $this->getParameterToAttributeMap('category');
            
            foreach ($map as $parameter => $attribute) {
                $attributeValue = $this->getAttributeValue($c, $attribute);
                
                if ($attributeValue) {
                    $data[$parameter] = $this->filter($attributeValue);
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Returns product data for Server-Side tracking.
     *
     * @param int $productId
     * @param int $customerId
     * @return array
     */
    public function getProductDataForServer($productId = 0, $customerId = 0)
    {
        $isEnabled = $this->isEventEnabled('ViewContent', true);
        
        $d = [];
        
        $i = 0;
        
        if ($isEnabled) {
            if ($productId) {
                $userData = $this->getUserDataForServer($customerId);
                $dataRaw  = $this->getProductData($productId);
                
                $data = null;
                if (array_key_exists('data', $dataRaw)) {
                    $data = $dataRaw['data'];
                }
                
                if (null == $data) {
                    $customData = [];
                } else {
                    $customData = $data;
                }
                
                $currentUrl = $this->getCurrentUrl();
                
                $d['data'][0]['event_name'] = 'ViewContent';
                $d['data'][0]['event_time'] = time();
                $d['data'][0]['event_id']   = $this->generateEventId(
                    'ViewContent',
                    $this->productId,
                    'product'
                );
                
                // Optional
                $d['data'][0]['event_source_url'] = $currentUrl;
                
                $d['data'][0]['user_data']   = $userData;
                $d['data'][0]['custom_data'] = $customData;
                
                // Optional
                $d['data'][0]['opt_out'] = false;
                
                $i = 1;
            }
        }
        
        // Add PageView based on config
        $isPageViewEnabled = $this->isEventEnabled('PageView', true);
        $isPageViewWithAll = $this->isPageViewWithAll(true);
        
        if ($isPageViewEnabled && $isPageViewWithAll) {
            $pageViewEvent = $this->getDataForServerPageViewEvent($customerId);
            
            if ($i) {
                $pageViewData  = $pageViewEvent['data'][0];
                $d['data'][$i] = $pageViewData;
            } else {
                $d = $pageViewEvent;
            }
        }
        
        return $d;
    }
    
    /**
     * Returns product data needed for tracking.
     *
     * @param int $id
     * @return array
     */
    public function getProductData($id = 0)
    {
        if ($id) {
            $p = $this->getProduct($id);
        } else {
            $p = $this->getProduct();
        }
        
        if (null == $p) {
            return [];
        }
        
        // Get product data with contents
        $data = $this->getProductDataWithContents($p);
        
        if (null == $data) {
            return [];
        }
        
        $d = [];
        
        $d['data']                              = $data;
        $d['data_with_content_ids']             = $this->dataWithContentIds;
        $d['contents_with_ids']                 = $this->contentsWithIds;
        $d['bundle_product_options_data']       = $this->bundleProductOptionsData;
        $d['configurable_product_options_data'] = $this->configurableProductOptionsData;
        $d['product_id']                        = $this->productId;
        $d['product_type']                      = $this->productType;
        
        return $d;
    }
    
    /**
     * Returns product data array with contents
     *
     * @param \Magento\Catalog\Model\Product $p
     * @return array
     */
    public function getProductDataWithContents($p)
    {
        $data           = [];
        $contents       = [];
        $contentsSingle = [];
        $i              = 0;
        
        $dataContentIds = [];
        $this->dataWithContentIds = [];
        
        $currencyCode = $this->getCurrentCurrencyCode();
        $value        = $this->formatPrice($this->getProductPrice($p));
        
        if ($this->getIdent() == 'id') {
            $sku = $this->filter($p->getId());
        } else {
            $sku = $this->filter($p->getSku());
        }
        
        $productName  = $this->filter($p->getName());
        $productType  = $p->getTypeId();
        
        // Save product ID and type for easy access
        $this->productId   = $p->getEntityId();
        $this->productType = $productType;
        
        $contentsSingle[0]['id']         = $sku;
        $contentsSingle[0]['quantity']   = 1;
        $contentsSingle[0]['item_price'] = $value;
        
        $dataContentIds['content_ids'][0] = $sku;
        
        // Custom Parameters
        $attributeValue = '';
        $map = $this->getParameterToAttributeMap();
        
        foreach ($map as $parameter => $attribute) {
            $attributeValue = $this->getAttributeValue($p, $attribute);
            
            if ($attributeValue) {
                $contentsSingle[0][$parameter] = $this->filter($attributeValue);
                $dataContentIds[$parameter] = $this->filter($attributeValue);
            }
        }
        
        // Set content_name if it is not empty
        $contentNameAttrib = $this->getConfig(
            'apptrian_facebookpixel/product/content_name',
            $this->getStoreId()
        );
        if ($contentNameAttrib) {
            $contentName = $this->getAttributeValue($p, $contentNameAttrib);
            if ($contentName) {
                $data['content_name'] = $this->filter($contentName);
                $dataContentIds['content_name'] = $this->filter($contentName);
            }
        }
        
        // Set content_category if it is not empty
        $contentCategoryAttrib = $this->getConfig(
            'apptrian_facebookpixel/product/content_category',
            $this->getStoreId()
        );
        if ($contentCategoryAttrib) {
            $contentCategory = $this->getAttributeValue($p, $contentCategoryAttrib);
            if ($contentCategory) {
                $data['content_category'] = $this->filter($contentCategory);
                $dataContentIds['content_category'] = $this->filter($contentCategory);
            }
        }
        
        // Set google_product_category if it is not empty
        $googleProductCategoryAttrib = $this->getConfig(
            'apptrian_facebookpixel/product/google_product_category',
            $this->getStoreId()
        );
        if ($googleProductCategoryAttrib) {
            $googleProductCategory = $this->getAttributeValue($p, $googleProductCategoryAttrib);
            if ($googleProductCategory) {
                $data['google_product_category'] = $this->filter($googleProductCategory);
                $dataContentIds['google_product_category'] = $this->filter($googleProductCategory);
            }
        }
        
        $data['contents'] = $contentsSingle;
        
        $data['content_type'] = 'product';
        $dataContentIds['content_type'] = 'product';
        
        // Event options
        // 1 = parent
        // 2 = children
        // 3 = both
        $option = (int) $this->getConfig(
            'apptrian_facebookpixel/product/ident_' . $productType,
            $this->getStoreId()
        );
        
        // Check product type and find all variant SKUs
        if ($productType == 'configurable'
            || $productType == 'bundle'
            || $productType == 'grouped'
        ) {
            $children = $this->getProductChildren($p);
            $childId  = 0;
            $childSKu = '';
            
            foreach ($children as $child) {
                // Must load child product to get all data
                $child = $this->getProductById($child->getEntityId());
                
                if ($productType == 'configurable') {
                    $this->allowedProducts[$i] = $child;
                }
                
                $childId  = $this->filter($child->getEntityId());
                $childSku = $this->filter($child->getSku());
                
                // Required parameter id
                if ($this->getIdent() == 'id') {
                    $contents[$i]['id'] = $childId;
                    $this->contentsWithIds[$childId]['id'] = $childId;
                    $dataContentIds['content_ids'][$i] = $childId;
                    if ($option == 3) {
                        // Optional parameter item_group_id
                        $contents[$i]['item_group_id'] = $this->productId;
                        $this->contentsWithIds[$childId]['item_group_id'] = $this->productId;
                    }
                } else {
                    $contents[$i]['id'] = $childSku;
                    $this->contentsWithIds[$childId]['id'] = $childSku;
                    $dataContentIds['content_ids'][$i] = $childSku;
                    if ($option == 3) {
                        // Optional parameter item_group_id
                        $contents[$i]['item_group_id'] = $sku;
                        $this->contentsWithIds[$childId]['item_group_id'] = $sku;
                    }
                }
                
                // Required parameter quantity
                $contents[$i]['quantity'] = 1;
                $this->contentsWithIds[$childId]['quantity'] = 1;
                
                // Optional parameter item_price
                $contents[$i]['item_price'] = $this->formatPrice($this->getProductPrice($child));
                $this->contentsWithIds[$childId]['item_price'] = $this->formatPrice($this->getProductPrice($child));
                
                // Optional custom parameters
                foreach ($map as $parameter => $attribute) {
                    $attributeValue = $this->getAttributeValue($child, $attribute);
                    
                    if ($attributeValue) {
                        $contents[$i][$parameter] = $this->filter($attributeValue);
                        $this->contentsWithIds[$childId][$parameter] = $this->filter($attributeValue);
                    }
                }
                
                // Do not forget to increment
                $i++;
            }
            
            // Must be done like this because you need contentsWithIds in any case
            if ($option !== 1) {
                // Reset contents
                $data['contents'] = [];
                // Set contents
                $data['contents'] = $contents;
                
                if ($option == 3) {
                    $dataContentIds['item_group_id'] = $sku;
                }
            } else {
                // 0 = product
                // 1 = product_group
                $useProductGroup = (int) $this->getConfig(
                    'apptrian_facebookpixel/product/content_type_' . $productType,
                    $this->getStoreId()
                );
                
                if ($useProductGroup) {
                    $data['content_type'] = 'product_group';
                    $dataContentIds['content_type'] = 'product_group';
                }
                
                // Reset content_ids
                $dataContentIds['content_ids'] = [];
                
                // Set content_ids
                $dataContentIds['content_ids'][0] = $sku;
            }
            
            // If bundle product type set options array
            if ($productType == 'bundle') {
                $this->bundleProductOptionsData = $this->getBundleProductOptionsData($p);
            }
            
            // If configurable product type set options array
            if ($productType == 'configurable') {
                $this->configurableProductOptionsData = $this->getConfigurableProductOptionsData($p);
            }
        } else {
            // simple, downlodable, virtual
            if ($option == 3) {
                $parentSku = $this->getParentProductSku($p->getEntityId());
                
                if ($parentSku) {
                    // Optional parameter item_group_id
                    $data['contents'][0]['item_group_id'] = $parentSku;
                    $dataContentIds['item_group_id']      = $parentSku;
                }
            }
        }
        
        $data['value']              = $value;
        $dataContentIds['value']    = $value;
        $data['currency']           = $currencyCode;
        $dataContentIds['currency'] = $currencyCode;
        
        $useContentIds = $this->isUseContentIdsEnabled();
        
        if ($useContentIds) {
            $this->dataWithContentIds = $dataContentIds;
        }
        
        return $data;
    }
    
    /**
     * Returns configuration value for use_content_ids
     *
     * @return bool
     */
    public function isUseContentIdsEnabled()
    {
        return (bool) $this->getConfig(
            'apptrian_facebookpixel/product/use_content_ids',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns configuration value for firing mode for Facebook Conversions API.
     *
     * @return bool
     */
    public function getFiringMode()
    {
        return (int) $this->getConfig(
            'apptrian_facebookpixel/server/firing_mode',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns configuration value for logging of events.
     *
     * @return bool
     */
    public function isLogEventsEnabled()
    {
        return (bool) $this->getConfig(
            'apptrian_facebookpixel/server/log_events',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns configuration value for event.
     *
     * @param string $event
     * @param bool $server
     * @return bool
     */
    public function isEventEnabled($event, $server = false)
    {
        $evt = $this->convertToLowercase($event);
        
        $clientEnabled = (bool) $this->getConfig(
            'apptrian_facebookpixel/client/enabled',
            $this->getStoreId()
        );
        
        $clientConfig = (bool) $this->getConfig(
            'apptrian_facebookpixel/client/' . $evt . '_enabled',
            $this->getStoreId()
        );
        
        $serverEnabled = (bool) $this->getConfig(
            'apptrian_facebookpixel/server/enabled',
            $this->getStoreId()
        );
        
        $serverConfig = (bool) $this->getConfig(
            'apptrian_facebookpixel/server/' . $evt . '_enabled',
            $this->getStoreId()
        );
        
        $firingMode = $this->getFiringMode();
        
        if ($server) {
            if ($serverEnabled) {
                return $serverConfig;
            } else {
                return false;
            }
        } else {
            if ($clientEnabled && $firingMode != 2) {
                return $clientConfig;
            } else {
                return false;
            }
        }
    }
    
    /**
     * Returns configuration value for API events sent via AJAX.
     *
     * @param string $event
     * @return bool
     */
    public function isApiEventEnabled($event)
    {
        $isEnabled  = $this->isEventEnabled($event, true);
        $firingMode = $this->getFiringMode();
        
        if ($isEnabled && $firingMode != 2) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns configuration value for PageView with all.
     *
     * @param bool $server
     * @return bool
     */
    public function isPageViewWithAll($server = false)
    {
        $r = 0;
        
        if ($server) {
            $r = (int) $this->getConfig(
                'apptrian_facebookpixel/server/pageview_all',
                $this->getStoreId()
            );
        } else {
            $r = (int) $this->getConfig(
                'apptrian_facebookpixel/client/pageview_all',
                $this->getStoreId()
            );
        }
        
        return $r;
    }
    
    /**
     * Returns configuration value for moving params outside contents.
     *
     * @param bool $server
     * @return int
     */
    public function isMoveParamsOutsideContentsEnabled($server = false)
    {
        $path = '';
        
        if ($server) {
            $path = 'apptrian_facebookpixel/server/move_params_outside_contents';
        } else {
            $path = 'apptrian_facebookpixel/client/move_params_outside_contents';
        }
        
        return (int) $this->getConfig($path, $this->getStoreId());
    }
    
    /**
     * Returns configuration value for detect_selected_sku
     *
     * @param string $productType
     * @param bool $server
     * @return bool
     */
    public function isDetectSelectedSkuEnabled($productType, $server = false)
    {
        $path = '';
        
        if ($server) {
            $path = 'apptrian_facebookpixel/server/';
        } else {
            $path = 'apptrian_facebookpixel/client/';
        }
        
        if ($productType == 'bundle'
            || $productType == 'configurable'
            || $productType == 'grouped'
        ) {
            $path .= 'detect_selected_sku_' . $productType;
        } else {
            $path .= 'detect_selected_sku';
        }
        
        return (bool) $this->getConfig($path, $this->getStoreId());
    }
    
    /**
     * Returns configuration value for Facebook Pixel.
     *
     * @return bool
     */
    public function isPixelEnabled()
    {
        return (bool) $this->getConfig(
            'apptrian_facebookpixel/client/enabled',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns configuration value for Pixel base code.
     *
     * @return bool
     */
    public function isBaseCodeEnabled()
    {
        return (bool) $this->getConfig(
            'apptrian_facebookpixel/client/base_code_enabled',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns configuration value for noscript.
     *
     * @return bool
     */
    public function isNoScriptEnabled()
    {
        return (bool) $this->getConfig(
            'apptrian_facebookpixel/client/noscript_enabled',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns configuration value for Facebook Conversions API.
     *
     * @return bool
     */
    public function isApiEnabled()
    {
        return (bool) $this->getConfig(
            'apptrian_facebookpixel/server/enabled',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns price decimal sign.
     *
     * @return string
     */
    public function getPriceDecimalSymbol()
    {
        $decimalSymbol = '';
        $locale        = $this->localeResolver->getLocale();
        $priceFormat   = $this->localeFormat->getPriceFormat($locale);
        $decimalSymbol = $priceFormat['decimalSymbol'];
        
        return $decimalSymbol;
    }
    
    /**
     * Returns order data for Client-Side tracking.
     *
     * @return array
     */
    public function getOrderDataForClient()
    {
        $isEnabled = $this->isEventEnabled('Purchase');
        
        if (!$isEnabled) {
            return [];
        }
        
        $data = $this->getOrderOrQuoteData('order');
        
        if (null == $data) {
            return [];
        }
        
        if ($this->isMoveParamsOutsideContentsEnabled()) {
            // Move parameters outside contents
            $data = $this->moveParamsOutsideContentsForClient($data);
        }
        
        $orderId = $this->checkoutSession->getLastRealOrder()->getId();
        
        if (!$orderId) {
            return [];
        }
        
        $d = [];
        
        $eventName = 'Purchase';
        
        $d['data']       = $data;
        $d['event_name'] = $eventName;
        $d['event_id']   = $this->generateEventId($eventName, $orderId, 'order', true);
        
        return $d;
    }
    
    /**
     * Returns order data for Server-Side tracking.
     *
     * @param int $customerId
     * @return array
     */
    public function getOrderDataForServer($customerId = 0)
    {
        $isEnabled = $this->isEventEnabled('Purchase', true);
        
        $d = [];
        
        $i = 0;
        
        if ($isEnabled) {
            $data = $this->getOrderOrQuoteData('order');
        
            if (null != $data) {
                $orderId = $this->checkoutSession->getLastRealOrder()->getId();
        
                if ($orderId) {
                    $eventName = 'Purchase';
                    $userData  = $this->getUserDataForServer($customerId);
                    
                    $d['data'][0]['event_name']  = $eventName;
                    $d['data'][0]['event_time']  = time();
                    $d['data'][0]['event_id']    = $this->generateEventId($eventName, $orderId, 'order');
                    
                    // Optional
                    $d['data'][0]['event_source_url'] = $this->getCurrentUrl();
                    
                    $d['data'][0]['user_data']   = $userData;
                    $d['data'][0]['custom_data'] = $data;
                    
                    // Optional
                    $d['data'][0]['opt_out'] = false;
                    
                    $i = 1;
                }
            }
        }
        
        // Add PageView based on config
        $isPageViewEnabled = $this->isEventEnabled('PageView', true);
        $isPageViewWithAll = $this->isPageViewWithAll(true);
        
        if ($isPageViewEnabled && $isPageViewWithAll) {
            $pageViewEvent = $this->getDataForServerPageViewEvent($customerId);
            
            if ($i) {
                $pageViewData  = $pageViewEvent['data'][0];
                $d['data'][$i] = $pageViewData;
            } else {
                $d = $pageViewEvent;
            }
        }
        
        return $d;
    }
    
    /**
     * Returns quote data for Client-Side tracking.
     *
     * @return array|null
     */
    public function getQuoteDataForClient()
    {
        $isEnabled = $this->isEventEnabled('InitiateCheckout');
        
        if (!$isEnabled) {
            return [];
        }
        
        $data = $this->getOrderOrQuoteData('quote');
        
        if (null == $data) {
            return [];
        }
        
        if ($this->isMoveParamsOutsideContentsEnabled()) {
            // Move parameters outside contents
            $data = $this->moveParamsOutsideContentsForClient($data);
        }
        
        $quoteId = $this->checkoutSession->getQuote()->getId();
        
        if (!$quoteId) {
            return [];
        }
        
        $d = [];
        
        $eventName = 'InitiateCheckout';
        
        $d['data']       = $data;
        $d['event_name'] = $eventName;
        $d['event_id']   = $this->generateEventId($eventName, $quoteId, 'quote', true);
        
        return $d;
    }
    
    /**
     * Returns quote data for Server-Side tracking.
     *
     * @param int $customerId
     * @return array|null
     */
    public function getQuoteDataForServer($customerId = 0)
    {
        $isEnabled = $this->isEventEnabled('InitiateCheckout', true);
        
        $d = [];
        
        $i = 0;
        
        if ($isEnabled) {
            $data = $this->getOrderOrQuoteData('quote');
        
            if (null != $data) {
                $quoteId = $this->checkoutSession->getQuote()->getId();
        
                if ($quoteId) {
                    $eventName = 'InitiateCheckout';
                    $userData  = $this->getUserDataForServer($customerId);
                    
                    $d['data'][0]['event_name']  = $eventName;
                    $d['data'][0]['event_time']  = time();
                    $d['data'][0]['event_id']    = $this->generateEventId($eventName, $quoteId, 'quote');
                    
                    // Optional
                    $d['data'][0]['event_source_url'] = $this->getCurrentUrl();
                    
                    $d['data'][0]['user_data']   = $userData;
                    $d['data'][0]['custom_data'] = $data;
                    
                    // Optional
                    $d['data'][0]['opt_out'] = false;
                    
                    $i = 1;
                }
            }
        }
        
        // Add PageView based on config
        $isPageViewEnabled = $this->isEventEnabled('PageView', true);
        $isPageViewWithAll = $this->isPageViewWithAll(true);
        
        if ($isPageViewEnabled && $isPageViewWithAll) {
            $pageViewEvent = $this->getDataForServerPageViewEvent($customerId);
            
            if ($i) {
                $pageViewData  = $pageViewEvent['data'][0];
                $d['data'][$i] = $pageViewData;
            } else {
                $d = $pageViewEvent;
            }
        }
        
        return $d;
    }
    
    /**
     * Returns data needed for tracking based on config group.
     *
     * @param string $group
     * @return array|null
     */
    public function getOrderOrQuoteData($group)
    {
        $obj = null;
        
        if ($group == 'order') {
            $obj = $this->checkoutSession->getLastRealOrder();
        }
        
        if ($group == 'quote') {
            $obj = $this->checkoutSession->getQuote();
        }
        
        if (null == $obj) {
            return null;
        }
        
        $objId = $obj->getId();
        
        if (!$objId) {
            return null;
        }
        
        $allItems        = $obj->getAllItems();
        $allVisibleItems = $obj->getAllVisibleItems();

        $data         = [];
        $items        = [];
        $itemId       = '';
        $parentItemId = '';
        $i            = 0;
        $contents     = [];
        $product      = null;
        $productId    = 0;
        $productType  = '';
        $parent       = null;
        $parentId     = 0;
        $storeId      = $this->getStoreId();
        $numItems     = 0;
        $taxFlag      = $this->getDisplayTaxFlag();

        // Custom Parameters
        $attributeValue = '';
        $map = $this->getParameterToAttributeMap($group);

        foreach ($allVisibleItems as $item) {
            $itemId = $item->getItemId();
            
            $items[$itemId]['item_id']        = $itemId;
            $items[$itemId]['parent_item_id'] = $item->getParentItemId();
            $items[$itemId]['product_id']     = $item->getProductId();
            $items[$itemId]['product_type']   = $item->getProductType();
            $items[$itemId]['sku']            = $this->filter($item->getSku());
            $items[$itemId]['name']           = $this->filter($item->getName());
            $items[$itemId]['store_id']       = $item->getStoreId();
            
            if ($taxFlag) {
                $items[$itemId]['price'] = $this->formatPrice($item->getPriceInclTax());
            } else {
                $items[$itemId]['price'] = $this->formatPrice($item->getPrice());
            }
            
            if ($group == 'quote') {
                $items[$itemId]['qty'] = round($item->getQty(), 0);
            } else {
                $items[$itemId]['qty'] = round($item->getQtyOrdered(), 0);
            }
        }

        foreach ($allItems as $item) {
            $itemId       = $item->getItemId();
            $parentItemId = $item->getParentItemId();
            
            if ($parentItemId) {
                $items[$parentItemId]['children'][$itemId]['item_id']        = $itemId;
                $items[$parentItemId]['children'][$itemId]['parent_item_id'] = $parentItemId;
                $items[$parentItemId]['children'][$itemId]['product_id']     = $item->getProductId();
                $items[$parentItemId]['children'][$itemId]['product_type']   = $item->getProductType();
                $items[$parentItemId]['children'][$itemId]['sku']            = $this->filter($item->getSku());
                $items[$parentItemId]['children'][$itemId]['name']           = $this->filter($item->getName());
                $items[$parentItemId]['children'][$itemId]['store_id']       = $item->getStoreId();
                
                if ($taxFlag) {
                    $items[$parentItemId]['children'][$itemId]['price'] = $this->formatPrice($item->getPriceInclTax());
                } else {
                    $items[$parentItemId]['children'][$itemId]['price'] = $this->formatPrice($item->getPrice());
                }
                
                if ($group == 'quote') {
                    if ($items[$parentItemId]['product_type'] == 'configurable') {
                        $q = $items[$parentItemId]['qty'];
                        $items[$parentItemId]['children'][$itemId]['qty'] = $q;
                    } else {
                        $items[$parentItemId]['children'][$itemId]['qty'] = round($item->getQty(), 0);
                    }
                } else {
                    $items[$parentItemId]['children'][$itemId]['qty'] = round($item->getQtyOrdered(), 0);
                }
            }
        }
        
        foreach ($items as $item) {
            $productId   = $item['product_id'];
            $productType = $item['product_type'];
            $storeId     = $item['store_id'];
            
            // Event options
            // 1 = poduct/parent
            // 2 = children/child
            // 3 = children/child/product and parent
            $option = (int) $this->getConfig(
                'apptrian_facebookpixel/' . $group . '/ident_' . $productType,
                $storeId
            );
            
            $product    = $this->getProductById($productId, $storeId);
            $productSku = $this->filter($product->getSku());
            
            if ($productType == 'bundle' || $productType == 'configurable') {
                if ($option == 1) {
                    // Option 1 means show parent SKU only
                    $qty = $item['qty'];
                    
                    if ($this->getIdent() == 'id') {
                        $contents[$i]['id'] = $productId;
                    } else {
                        $contents[$i]['id'] = $productSku;
                    }
                    
                    $contents[$i]['quantity']   = $qty;
                    $contents[$i]['item_price'] = $item['price'];
                    
                    // Custom Parameters
                    $contents[$i] = $this->addCustomParameters($map, $product, $contents[$i]);
                    
                    $numItems += $qty;
                    
                    $i++;
                } else {
                    // Option 2. or 3. means show children SKUs
                    
                    $children  = $item['children'];
                    
                    foreach ($children as $child) {
                        $childProductId = $child['product_id'];
                        $childProduct   = $this->getProductById($childProductId, $storeId);
                        
                        $qty = $child['qty'];
                        
                        if ($productType == 'bundle') {
                            // Budle products may have global qty higher than 1
                            $parentItemId = $child['parent_item_id'];
                            $globalQty    = $items[$parentItemId]['qty'];
                            $qty          = $qty * $globalQty;
                        }
                        
                        if ($this->getIdent() == 'id') {
                            $contents[$i]['id'] = $this->filter($childProduct->getId());
                        } else {
                            $contents[$i]['id'] = $this->filter($childProduct->getSku());
                        }
                        
                        $contents[$i]['quantity']   = $qty;
                        $contents[$i]['item_price'] = $child['price'];
                        
                        // For configurable you must use congfigurable price
                        // child price is 0
                        if ($productType == 'configurable') {
                            $contents[$i]['item_price'] = $item['price'];
                        }
                        
                        if ($option == 3) {
                            // Option 3 add parent product SKU on children
                            $contents[$i]['item_group_id'] = $this
                                ->getItemGroupIdIdentifier(null, $productId, $productSku);
                        }
                        
                        // Custom Parameters
                        $contents[$i] = $this->addCustomParameters($map, $childProduct, $contents[$i]);
                        
                        $numItems += $qty;
                        
                        $i++;
                    }
                }
            } else {
                // grouped, simple, virtual, downloadable products
                
                $qty = $item['qty'];
                
                if ($this->getIdent() == 'id') {
                    $contents[$i]['id'] = $productId;
                } else {
                    $contents[$i]['id'] = $productSku;
                }
                
                $contents[$i]['quantity']   = $qty;
                $contents[$i]['item_price'] = $item['price'];
                
                // Reset parent ID
                $parentId = 0;
                
                if ($productType == 'grouped') {
                    if ($option == 3) {
                        // Get parent grouped product ID
                        $parentId = $this->getParentGroupedProductId($productId);
                    }
                } else {
                    if ($option == 2) {
                        // Get parent product ID
                        $parentId = $this->getParentProductId($productId);
                    }
                }
                
                if ($parentId) {
                    $parent = $this->getProductById($parentId, $storeId);
                    if ($parent) {
                        $contents[$i]['item_group_id'] = $this->getItemGroupIdIdentifier($parent);
                    }
                }
                
                // Custom Parameters
                $contents[$i] = $this->addCustomParameters($map, $product, $contents[$i]);
                
                $numItems += $qty;
                
                $i++;
            }
        }
        
        // Check if there are items and if not return null
        if (empty($contents)) {
            return null;
        }
        
        // Order ID
        $orderIdParam = (string) $this->getConfig(
            'apptrian_facebookpixel/' . $group . '/order_id_param',
            $storeId
        );
        if ($orderIdParam) {
            $data[$orderIdParam] = (string) $obj->getId();
        }
        
        // Order increment ID
        $orderIncrementIdParam = (string) $this->getConfig(
            'apptrian_facebookpixel/' . $group . '/order_increment_id_param',
            $storeId
        );
        if ($orderIncrementIdParam) {
            $data[$orderIncrementIdParam] = (string) $obj->getIncrementId();
        }
        
        // Quote ID
        $quoteIdParam = (string) $this->getConfig(
            'apptrian_facebookpixel/' . $group . '/quote_id_param',
            $storeId
        );
        if ($quoteIdParam) {
            if ($group == 'quote') {
                $data[$quoteIdParam] = (string) $obj->getId();
            } else {
                $data[$quoteIdParam] = (string) $obj->getQuoteId();
            }
        }
        
        $data['contents']     = $contents;
        $data['content_type'] = 'product';
        $data['num_items']    = $numItems;
        $data['value']        = $this->formatPrice($obj->getGrandTotal());
        
        if ($group == 'quote') {
            $data['currency'] = $obj->getQuoteCurrencyCode();
        } else {
            $data['currency'] = $obj->getOrderCurrencyCode();
        }
        
        return $data;
    }
    
    /**
     * Adds custom parameters to contents item.
     *
     * @param array $map
     * @param \Magento\Catalog\Model\Product $product
     * @param array $contentsItem
     * @return array
     */
    public function addCustomParameters($map, $product, $contentsItem)
    {
        foreach ($map as $parameter => $attribute) {
            $attributeValue = $this->getAttributeValue(
                $product,
                $attribute
            );
            
            if ($attributeValue) {
                $contentsItem[$parameter] = $this->filter($attributeValue);
            }
        }
        
        return $contentsItem;
    }
    
    /**
     * Returns item_group_id identifier based on config.
     *
     * @param \Magento\Catalog\Model\Product $p
     * @param string $pId
     * @param string $pSku
     * @return string
     */
    public function getItemGroupIdIdentifier($p = null, $pId = '', $pSku = '')
    {
        $igi = '';
        
        if ($this->getIdent() == 'id') {
            if ($p !== null) {
                $igi = $this->filter($p->getId());
            } else {
                $igi = $pId;
            }
        } else {
            if ($p !== null) {
                $igi = $this->filter($p->getSku());
            } else {
                $igi = $pSku;
            }
        }
        
        return $igi;
    }
    
    /**
     * Returns search data for Client-Side tracking.
     *
     * @return array
     */
    public function getSearchDataForClient()
    {
        $isEnabled = $this->isEventEnabled('Search');
        
        if (!$isEnabled) {
            return [];
        }
        
        $d = [];
        
        $searchEventName = $this->getSearchEventName();
        $data            = $this->getSearchData();
        
        if ($searchEventName && !empty($data)) {
            $searchParamName = $this->getSearchParamName();
            $searchString    = $data[$searchParamName];
            
            $d['data']       = $data;
            $d['event_name'] = $searchEventName;
            $d['event_id']   = $this->generateEventId(
                $searchEventName,
                $searchString,
                'search',
                true
            );
        }
        
        return $d;
    }
    
    /**
     * Returns search data for Server-Side tracking.
     *
     * @param int|string $customerId
     * @return array
     */
    public function getSearchDataForServer($customerId = 0)
    {
        $isEnabled = $this->isEventEnabled('Search', true);
        
        $d = [];
        
        $i = 0;
        
        if ($isEnabled) {
            $searchEventName = $this->getSearchEventName();
            $data            = $this->getSearchData();
            
            if ($searchEventName && !empty($data)) {
                $searchParamName = $this->getSearchParamName();
                $searchString    = $data[$searchParamName];
                $userData        = $this->getUserDataForServer($customerId);
                
                $d['data'][0]['event_name'] = $searchEventName;
                $d['data'][0]['event_time'] = time();
                $d['data'][0]['event_id']   = $this->generateEventId($searchEventName, $searchString, 'search');
                
                // Optional
                $d['data'][0]['event_source_url'] = $this->getCurrentUrl();
                
                $d['data'][0]['user_data']   = $userData;
                $d['data'][0]['custom_data'] = $data;
                
                // Optional
                $d['data'][0]['opt_out'] = false;
                
                $i = 1;
            }
        }
        
        // Add PageView based on config
        $isPageViewEnabled = $this->isEventEnabled('PageView', true);
        $isPageViewWithAll = $this->isPageViewWithAll(true);
        
        if ($isPageViewEnabled && $isPageViewWithAll) {
            $pageViewEvent = $this->getDataForServerPageViewEvent($customerId);
            
            if ($i) {
                $pageViewData  = $pageViewEvent['data'][0];
                $d['data'][$i] = $pageViewData;
            } else {
                $d = $pageViewEvent;
            }
        }
        
        return $d;
    }
    
    /**
     * Returns category data needed for tracking.
     *
     * @return array|null
     */
    public function getSearchData()
    {
        $data = [];
        
        $requestParams = explode(
            ',',
            (string) $this->getConfig(
                'apptrian_facebookpixel/search/request_params',
                $this->getStoreId()
            )
        );
        
        $searchStrings = [];
        $p = '';
        
        foreach ($requestParams as $param) {
            // If prameter is array
            if (strpos($param, '[') !== false) {
                $p = substr($param, 0, strpos($param, '['));
            } else {
                $p = $param;
            }
            
            $rp = $this->request->getParam($p);
            
            if (!empty($rp)) {
                if (is_array($rp)) {
                    $v = trim((string) implode(',', $rp), ',');
                    if (!empty($v)) {
                        $searchStrings[] = $v;
                    }
                }
                
                if (is_string($rp)) {
                    $searchStrings[] = $this->filter(trim((string) $rp));
                }
            }
        }
        
        $paramName    = $this->getSearchParamName();
        $searchString = implode(',', $searchStrings);

        if ($paramName && $searchString) {
            $data[$paramName] = $searchString;
        }
        
        return $data;
    }
    
    /**
     * Returns configuration value for event name
     *
     * @param string $group
     * @return string
     */
    public function getEventName($group)
    {
        return $this->filter(
            (string) $this->getConfig(
                'apptrian_facebookpixel/' . $group . '/event_name',
                $this->getStoreId()
            )
        );
    }
    
    /**
     * Returns configuration value for category event name
     *
     * @return string
     */
    public function getCategoryEventName()
    {
        if ($this->categoryEventName === null) {
            $this->categoryEventName = $this->getEventName('category');
        }
        
        return $this->categoryEventName;
    }
    
    /**
     * Returns configuration value for search event name
     *
     * @return string
     */
    public function getSearchEventName()
    {
        if ($this->searchEventName === null) {
            $this->searchEventName = $this->getEventName('search');
        }
        
        return $this->searchEventName;
    }
    
    /**
     * Returns configuration value for event param
     *
     * @param string $group
     * @return string
     */
    public function getParamName($group)
    {
        return $this->filter(
            (string) $this->getConfig(
                'apptrian_facebookpixel/' . $group . '/param_name',
                $this->getStoreId()
            )
        );
    }
    
    /**
     * Returns configuration value for search event name
     *
     * @return string
     */
    public function getSearchParamName()
    {
        if ($this->searchParamName === null) {
            $this->searchParamName = $this->getParamName('search');
        }
        
        return $this->searchParamName;
    }
    
    /**
     * Based on provided configuration path returns configuration value.
     *
     * @param string $configPath
     * @param string|int $scope
     * @return string
     */
    public function getConfig($configPath, $scope = 'default')
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }
    
    /**
     * Removes new lines and tabs from the string and prepares string.
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        return trim(str_replace(["\t","\n","\r\n","\r"], '', (string) $str));
    }
    
    /**
     * Returns store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->store === null) {
            $this->store = $this->storeManager->getStore();
        }
        
        return $this->store;
    }
    
    /**
     * Returns Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->storeId = $this->getStore()->getId();
        }
        
        return $this->storeId;
    }
    
    /**
     * Returns base currency code
     * (3 letter currency code like USD, GBP, EUR, etc.)
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        if ($this->baseCurrencyCode === null) {
            $this->baseCurrencyCode = strtoupper(
                $this->getStore()->getBaseCurrencyCode()
            );
        }
        
        return $this->baseCurrencyCode;
    }
    
    /**
     * Returns current currency code
     * (3 letter currency code like USD, GBP, EUR, etc.)
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        if ($this->currentCurrencyCode === null) {
            $this->currentCurrencyCode = strtoupper(
                $this->getStore()->getCurrentCurrencyCode()
            );
        }
        
        return $this->currentCurrencyCode;
    }
    
    /**
     * Returns flag based on "Stores > Cofiguration > Sales > Tax
     * > Price Display Settings > Display Product Prices In Catalog"
     * Returns 0 or 1 instead of 1, 2, 3.
     *
     * @return int
     */
    public function getDisplayTaxFlag()
    {
        if ($this->taxDisplayFlag === null) {
            // Tax Display
            // 1 - excluding tax
            // 2 - including tax
            // 3 - including and excluding tax
            $flag = $this->taxConfig->getPriceDisplayType($this->getStoreId());
            
            // 0 means price excluding tax, 1 means price including tax
            if ($flag == 1) {
                $this->taxDisplayFlag = 0;
            } else {
                $this->taxDisplayFlag = 1;
            }
        }
        
        return $this->taxDisplayFlag;
    }
    
    /**
     * Returns Stores > Cofiguration > Sales > Tax > Calculation Settings
     * > Catalog Prices configuration value
     *
     * @return int
     */
    public function getCatalogTaxFlag()
    {
        // Are catalog product prices with tax included or excluded?
        if ($this->taxCatalogFlag === null) {
            $this->taxCatalogFlag = (int) $this->getConfig(
                'tax/calculation/price_includes_tax',
                $this->getStoreId()
            );
        }
        
        // 0 means excluded, 1 means included
        return $this->taxCatalogFlag;
    }
    
    /**
     * Returns product price.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrice($product)
    {
        $price = 0.0;
        
        switch ($product->getTypeId()) {
            case 'bundle':
                $price =  $this->getBundleProductPrice($product);
                break;
            case 'configurable':
                $price = $this->getConfigurableProductPrice($product);
                break;
            case 'grouped':
                $price = $this->getGroupedProductPrice($product);
                break;
            default:
                $price = $this->getFinalPrice($product);
        }
        
        return $price;
    }
    
    /**
     * Returns bundle product price.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getBundleProductPrice($product)
    {
        $includeTax = (bool) $this->getDisplayTaxFlag();
        
        return $this->getFinalPrice(
            $product,
            $product->getPriceModel()->getTotalPrices(
                $product,
                'min',
                $includeTax,
                1
            )
        );
    }
    
    /**
     * Returns configurable product price.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getConfigurableProductPrice($product)
    {
        if ($product->getFinalPrice() === 0) {
            $simpleCollection = $product->getTypeInstance()
                ->getUsedProducts($product);
            
            foreach ($simpleCollection as $simpleProduct) {
                if ($simpleProduct->getPrice() > 0) {
                    return $this->getFinalPrice($simpleProduct);
                }
            }
        }
        
        return $this->getFinalPrice($product);
    }
    
    /**
     * Returns grouped product price.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getGroupedProductPrice($product)
    {
        $assocProducts = $product->getTypeInstance(true)
            ->getAssociatedProductCollection($product)
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('tax_class_id')
            ->addAttributeToSelect('tax_percent');
        
        $minPrice = INF;
        foreach ($assocProducts as $assocProduct) {
            $minPrice = min($minPrice, $this->getFinalPrice($assocProduct));
        }
        
        return $minPrice;
    }
    
    /**
     * Returns final price.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $price
     * @return string
     */
    public function getFinalPrice($product, $price = null)
    {
        if ($price === null) {
            $price = $product->getFinalPrice();
        }
        
        if ($price === null) {
            $price = $product->getData('special_price');
        }
        
        $productType = $product->getTypeId();
        
        // 1. Convert to current currency if needed
        
        // Convert price if base and current currency are not the same
        // Except for configurable products they already have currency converted
        if (($this->getBaseCurrencyCode() !== $this->getCurrentCurrencyCode())
            && $productType != 'configurable'
        ) {
            // Convert to from base currency to current currency
            $price = $this->getStore()->getBaseCurrency()
                ->convert($price, $this->getCurrentCurrencyCode());
        }
        
        // 2. Apply tax if needed
        
        // Simple, Virtual, Downloadable products price is without tax
        // Grouped products have associated products without tax
        // Bundle products price already have tax included/excluded
        // Configurable products price already have tax included/excluded
        if ($productType != 'configurable' && $productType != 'bundle') {
            // If display tax flag is on and catalog tax flag is off
            if ($this->getDisplayTaxFlag() && !$this->getCatalogTaxFlag()) {
                $price = $this->catalogHelper->getTaxPrice(
                    $product,
                    $price,
                    true,
                    null,
                    null,
                    null,
                    $this->getStoreId(),
                    false,
                    false
                );
            }
        }
        
        // Case when catalog prices are with tax but display tax is set to
        // to exclude tax. Applies for all products except for bundle
        if ($productType != 'bundle') {
            // If display tax flag is off and catalog tax flag is on
            if (!$this->getDisplayTaxFlag() && $this->getCatalogTaxFlag()) {
                $price = $this->catalogHelper->getTaxPrice(
                    $product,
                    $price,
                    false,
                    null,
                    null,
                    null,
                    $this->getStoreId(),
                    true,
                    false
                );
            }
        }
        
        return $price;
    }
    
    /**
     * Returns formated price.
     *
     * @param string $price
     * @param bool $toFloat
     * @param string $currencyCode
     * @return string
     */
    public function formatPrice($price, $toFloat = true, $currencyCode = '')
    {
        $formatedPrice = number_format($price, 2, '.', '');
        
        if ($currencyCode) {
            return $formatedPrice . ' ' . $currencyCode;
        } else {
            if ($toFloat) {
                return round((float)$formatedPrice, 2);
            } else {
                return $formatedPrice;
            }
        }
    }
    
    /**
     * Returns object attribute value or values.
     * Third param is optional, if set to false it will return array of values
     * for multiselect attributes.
     *
     * @param \Magento\Catalog\Model\Category|\Magento\Catalog\Model\Product $o
     * @param string $attrCode
     * @param bool $toString
     * @return string
     */
    public function getAttributeValue($o, $attrCode, $toString = true)
    {
        $attrValue = '';
        
        if ($o->getData($attrCode)) {
            $attrValue = $o->getAttributeText($attrCode);
            
            if (!$attrValue) {
                $attrValue = $o->getData($attrCode);
            }
        }
        
        if ($toString && is_array($attrValue)) {
            $attrValue = implode(', ', $attrValue);
        }
        
        return $attrValue;
    }
    
    /**
     * Returns array map from parameter mapping configuration.
     *
     * Default is 'product' but you can specify for mapping others.
     *
     * @param string $type
     * @return array
     */
    public function getParameterToAttributeMap($type = 'product')
    {
        $map = [];
        
        $data = $this->getConfig(
            'apptrian_facebookpixel/' . $type . '/mapping',
            $this->getStoreId()
        );
        
        if (!$data) {
            return $map;
        }
        
        $pairs = explode('|', (string) $data);
        
        foreach ($pairs as $pair) {
            $pairArray = explode('=', (string) $pair);
            
            if (isset($pairArray[0]) && isset($pairArray[1])) {
                $cleanedParameter = trim((string) $pairArray[0]);
                $cleanedAttribute = trim((string) $pairArray[1]);
                
                if ($cleanedParameter && $cleanedAttribute) {
                    $map[$cleanedParameter] = $cleanedAttribute;
                }
            }
        }
        
        return $map;
    }
    
    /**
     * Returns category object. If ID is not supplied it will return current category.
     *
     * @param null|int $id
     * @param int|string $storeId
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory($id = null, $storeId = '')
    {
        $category = null;
        
        if ($id) {
            $category = $this->getCategoryById($id, $storeId);
        } else {
            $category = $this->currentCategory->getCategory();
        }
        
        return $category;
    }
    
    /**
     * Returns category object loaded by ID.
     *
     * Used in getCategoryData() method to retreive category attributes.
     *
     * @param int $id
     * @param int|string $storeId
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategoryById($id, $storeId = '')
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        
        return $this->categoryFactory->create()->setStoreId($storeId)->load($id);
    }
    
    /**
     * Returns product object. If ID is not supplied it will return current product.
     *
     * @param null|int $id
     * @param int|string $storeId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($id = null, $storeId = '')
    {
        $product = null;
        
        if ($id) {
            $product = $this->getProductById($id, $storeId);
        } else {
            $product = $this->currentProduct->getProduct();
        }
        
        return $product;
    }
    
    /**
     * Returns product object loaded by ID.
     *
     * Used in getOrderOrQuoteData() method to retreive product attributes.
     *
     * @param int $id
     * @param int|string $storeId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductById($id, $storeId = '')
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        
        return $this->productFactory->create()->setStoreId($storeId)->load($id);
    }
    
    /**
     * Returns product object loaded by SKU.
     *
     * Used in getOrderOrQuoteData() method to retreive product attributes.
     *
     * @param string $sku
     * @param int|string $storeId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductBySku($sku, $storeId = '')
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        
        $product = $this->productFactory->create();
        return $product->setStoreId($storeId)->load($product->getIdBySku($sku));
    }
    
    /**
     * Returns product children collection.
     *
     * Used in getOrderOrQuoteData() method to retreive product attributes.
     *
     * @param \Magento\Catalog\Model\Product $p
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductChildren($p)
    {
        $children = [];
        $productType = $p->getTypeId();
        
        switch ($productType) {
            case 'bundle':
                $children = $p->getTypeInstance(true)->getSelectionsCollection(
                    $p->getTypeInstance(true)->getOptionsIds($p),
                    $p
                );
                break;
            case 'configurable':
                $children = $p->getTypeInstance()->getUsedProducts($p);
                break;
            case 'grouped':
                $children = $p->getTypeInstance()->getAssociatedProducts($p);
                break;
            default:
                $children = [];
        }
        
        return $children;
    }
    
    /**
     * Returns bundle product options data used for detection of selected SKUs.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getBundleProductOptionsData($product)
    {
        $optionsData = [];
        
        // Get all the selection products used in bundle product
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        
        $selectionArray = [];
        
        foreach ($selectionCollection as $selection) {
            $selectionArray['product_id'] = $selection->getProductId();
            $selectionArray['product_quantity'] = (float) $selection->getSelectionQty();
            $optionsData[$selection->getOptionId()][$selection->getSelectionId()] = $selectionArray;
        }
        
        return $optionsData;
    }
    
    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getConfigurableProductOptionsData($product)
    {
        $options = [];
        
        if (empty($this->allowedProducts)) {
            $this->allowedProducts = $this->getAllowedProducts($product);
        }
        
        $allowAttributes = $this->getAllowedAttributes($product);

        foreach ($this->allowedProducts as $p) {
            $productId = $p->getId();
            foreach ($allowAttributes as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $p
                    ->getData($productAttribute->getAttributeCode());
                
                $options[$productId][$productAttributeId] = $attributeValue;
            }
        }
        
        return $options;
    }
    
    /**
     * Get allowed attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAllowedAttributes($product)
    {
        return $product->getTypeInstance()->getConfigurableAttributes($product);
    }
    
    /**
     * Get Allowed Products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowedProducts($product)
    {
        $products = [];
        
        $allProducts = $product->getTypeInstance()->getUsedProducts($product);
        
        foreach ($allProducts as $p) {
            $products[] = $p;
        }
        
        return $products;
    }
    
    /**
     * Based on product ID returns parent product SKU or empty string for no parent sku.
     *
     * @param int $productId
     * @return string
     */
    public function getParentProductSku($productId)
    {
        $parentSku = '';
        
        $parentId = $this->getParentProductId($productId);
                
        if ($parentId) {
            $parent = $this->getProductById($parentId);
            if ($parent) {
                $parentSku = $this->filter($parent->getSku());
            }
        }
        
        return $parentSku;
    }
    
    /**
     * Based on product ID returns parent product ID or null for no parent.
     *
     * @param int $productId
     * @return null|int
     */
    public function getParentProductId($productId)
    {
        $parentId = null;
        
        // Configurable
        $parentId = $this->getParentConfigurableProductId($productId);
        if ($parentId) {
            return $parentId;
        }
        
        // Bundle
        $parentId = $this->getParentBundleProductId($productId);
        if ($parentId) {
            return $parentId;
        }
        
        // Grouped
        $parentId = $this->getParentGroupedProductId($productId);
        if ($parentId) {
            return $parentId;
        }
        
        return $parentId;
    }
    
    /**
     * Based on product ID returns parent bundle product ID or null for no parent.
     *
     * @param int $productId
     * @return null|int
     */
    public function getParentBundleProductId($productId)
    {
        $parentId = null;
        
        // Bundle
        $parentIds = $this->bundleProductModel
            ->getParentIdsByChild($productId);
        
        if (!empty($parentIds) && isset($parentIds[0])) {
            $parentId = $parentIds[0];
            return $parentId;
        }
        
        return $parentId;
    }
    
    /**
     * Based on product ID returns parent configurable product ID or null for no parent.
     *
     * @param int $productId
     * @return null|int
     */
    public function getParentConfigurableProductId($productId)
    {
        $parentId = null;
        
        // Configurable
        $parentIds = $this->configurableProductModel
            ->getParentIdsByChild($productId);
        
        if (!empty($parentIds) && isset($parentIds[0])) {
            $parentId = $parentIds[0];
            return $parentId;
        }
        
        return $parentId;
    }
    
    /**
     * Based on product ID returns parent grouped product ID or null for no parent.
     *
     * @param int $productId
     * @return null|int
     */
    public function getParentGroupedProductId($productId)
    {
        $parentId = null;
        
        // Grouped
        $parentIds = $this->groupedProductModel
            ->getParentIdsByChild($productId);
        
        if (!empty($parentIds) && isset($parentIds[0])) {
            $parentId = $parentIds[0];
            return $parentId;
        }
        
        return $parentId;
    }
    
    /**
     * Returns customer object loaded by customer ID.
     *
     * @param int $id
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerById($id)
    {
        if (!empty($id)) {
            return $this->customerFactory->create()->load($id);
        }
        
        return null;
    }
    
    /**
     * Returns address object loaded by address ID.
     *
     * @param int $id
     * @return \Magento\Customer\Model\Address
     */
    public function getCustomerAddressById($id)
    {
        return $this->addressFactory->create()->load($id);
    }
    
    /**
     * Returns region object loaded by region ID.
     *
     * @param int $id
     * @return \Magento\Directory\Model\Region
     */
    public function getRegionById($id)
    {
        return $this->regionFactory->create()->load($id);
    }
    
    /**
     * Returns region 2 letter code or name based on provided region ID.
     *
     * @param int $id
     * @return string
     */
    public function getRegionCodeOrNameById($id)
    {
        if (!$id) {
            return '';
        }
        
        $region = $this->getRegionById($id);
        $code   = $region->getCode();
        $name   = $region->getDefaultName();
        
        // FB wants only 2 letter codes otherwise name
        if ($this->stringLength($code) == 2) {
            return $code;
        } else {
            return $name;
        }
    }
    
    /**
     * Converts datetime string to date string.
     *
     * @param string $datetimeString
     * @return string
     */
    public function datetimeToDate($datetimeString)
    {
        $date = '';
        
        if ($datetimeString) {
            $date = date('Y-m-d', strtotime($datetimeString));
        }
        
        return $date;
    }
    
    /**
     * Returns string length.
     *
     * @param string $str
     * @return int
     */
    public function stringLength($str)
    {
        if (function_exists('mb_strlen')) {
            $length = mb_strlen($str, 'UTF-8');
        } else {
            $length = strlen($str);
        }
        
        return (int) $length;
    }
    
    /**
     * Converts string to lowercase.
     *
     * @param string $s
     * @return string
     */
    public function convertToLowercase($s)
    {
        if (function_exists('mb_strtolower')) {
            $str = mb_strtolower($s, 'UTF-8');
        } else {
            $str = strtolower($s);
        }
        
        return $str;
    }
    
    /**
     * Strips all non numeric characters.
     *
     * @param string $str
     * @return string
     */
    public function stripNonNumeric($str)
    {
        return preg_replace('/[^\p{N}]+/', '', $str);
    }
    
    /**
     * Strips all spaces and converts to lowercase.
     *
     * @param string $str
     * @return string
     */
    public function formatData($str)
    {
        return $this->filter(
            $this->convertToLowercase(
                preg_replace('/\s+/', '', $str)
            )
        );
    }
    
    /**
     * Returns visitor/customer IP address.
     *
     * @return string
     */
    public function getClientIpAddress()
    {
        return $this->remoteAddress->getRemoteAddress();
    }
    
    /**
     * Returns visitor/customer user agent.
     *
     * @return string
     */
    public function getClientUserAgent()
    {
        return $this->httpHeader->getHttpUserAgent();
    }
    
    /**
     * Returns visitor/customer _fbc cookie value.
     *
     * @param string $url
     * @return string
     */
    public function getFbc($url = '')
    {
        $fbc    = '';
        $fbclid = '';
        
        $fbc = $this->cookieManager->getCookie('_fbc');
        
        // If there is no cookie set try to get it from url query
        if (!$fbc) {
            // If url is provided try to get fbclid from it
            if ($url) {
                $fbclid = $this->getFbclidFromUrl($url);
            }
            
            // If fbclid is not found try request param
            if (!$fbclid) {
                $fbclid = $this->request->getParam('fbclid');
            }
            
            // If fbclid is not found try current url
            if (!$fbclid) {
                $url = $this->storeManager->getStore()->getCurrentUrl();
                $fbclid = $this->getFbclidFromUrl($url);
            }
            
            // If fbclid is not found try referer urls
            if (!$fbclid) {
                $url = $this->redirect->getRefererUrl();
                $fbclid = $this->getFbclidFromUrl($url);
            }
            
            if ($fbclid) {
                // The fbc event parameter value must be of the form version.subdomainIndex.creationTime.fbclid
                // https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc
                $fbc = 'fb.1.' . time() . '.' . $fbclid;
            }
        }
        
        return $fbc;
    }
    
    /**
     * Returns fbclid value from url.
     *
     * @param string $url
     * @return string
     */
    public function getFbclidFromUrl($url)
    {
        if (!$url) {
            return '';
        }
        
        $fbclid = '';
        $pos = strpos($url, '?');
        $queryString = '';
        $pairs = [];
        $pair  = [];
        
        if ($pos !== false) {
            $queryString = substr($url, $pos + 1);
            if ($queryString) {
                $pairs = explode('&', (string) $queryString);
                if (!empty($pairs) && is_array($pairs)) {
                    foreach ($pairs as $pairString) {
                        $pair = explode('=', (string) $pairString);
                        if (!empty($pair) && is_array($pair) && count($pair) == 2 && $pair[0] == 'fbclid') {
                            $fbclid = $pair[1];
                            // No need to continue the loop
                            break;
                        }
                    }
                }
            }
        }
        
        return $fbclid;
    }
    
    /**
     * Returns visitor/customer _fbp cookie value.
     *
     * @return string
     */
    public function getFbp()
    {
        return $this->cookieManager->getCookie('_fbp');
    }
    
    /**
     * Returns cookie_consent_enabled from config.
     *
     * @return int
     */
    public function isCookieConsentEnabled()
    {
        return (int) $this->getConfig(
            'apptrian_facebookpixel/miscellaneous/cookie_consent_enabled',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns consent_cookie_name from config.
     *
     * @return string
     */
    public function getConsentCookieName()
    {
        return $this->getConfig(
            'apptrian_facebookpixel/miscellaneous/consent_cookie_name',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns consent_cookie_key from config.
     *
     * @return string
     */
    public function getConsentCookieKey()
    {
        return $this->getConfig(
            'apptrian_facebookpixel/miscellaneous/consent_cookie_key',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns consent_cookie_value from config.
     *
     * @return string
     */
    public function getConsentCookieValue()
    {
        return $this->getConfig(
            'apptrian_facebookpixel/miscellaneous/consent_cookie_value',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns consent_button from config.
     *
     * @return string
     */
    public function getConsentButton()
    {
        return $this->getConfig(
            'apptrian_facebookpixel/miscellaneous/consent_button',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns visitor/customer consent cookie value.
     *
     * @return string
     */
    public function getConsentCookie()
    {
        // Get consent cookie name from config
        $cookieName = $this->getConsentCookieName();
        
        return $this->cookieManager->getCookie($cookieName);
    }
    
    /**
     * Returns cookie data from cookie string. If possible it will return array, in not a string.
     *
     * @param string $str
     * @return array|string
     */
    public function getCookieDataFromString($str)
    {
        $data = [];
        
        $data =  json_decode((string) $str, true);
        
        if (empty($data)) {
            // Try to get data manually
            if (strpos($str, '=') !== false) {
                $arr = explode('=', (string) $str);
                $key = '';
                $value = '';
                $k = 1;
                $count = count($arr);
                
                for ($i = 0; $i < $count; $i+=2) {
                    $key   = '';
                    $value = '';
                    
                    if (isset($arr[$i])) {
                        $key = trim((string) $arr[$i]);
                    }
                    
                    if (isset($arr[$k])) {
                        $value = trim((string) $arr[$k]);
                    }
                    
                    if ($key) {
                        $data[$key] = $value;
                    }
                    
                    $k+=2;
                }
            } else {
                $data = $str;
            }
        }
        
        return $data;
    }
    
    /**
     * Based on cookie and config params it will check for user consent.
     *
     * @return int
     */
    public function isConsentGranted()
    {
        $cookie = $this->getConsentCookie();
        $result = 0;
        
        if (!$cookie) {
            return $result;
        }
        
        $cookieData = $this->getCookieDataFromString($cookie);
        
        // Get consent cookie key from config
        $cookieKey = $this->getConsentCookieKey();
        
        // Get consent cookie value from config
        $cookieValue = $this->getConsentCookieValue();
        
        if ($cookieKey && $cookieValue) {
            if (is_array($cookieData) && isset($cookieData[$cookieKey])) {
                $value = $cookieData[$cookieKey];
                
                if ($value == $cookieValue) {
                    $result = 1;
                }
            }
        } elseif (!$cookieKey && $cookieValue) {
            if ($cookie == $cookieValue) {
                $result = 1;
            }
        } elseif ($cookieKey && !$cookieValue) {
            if (is_array($cookieData) && array_key_exists($cookieKey, $cookieData)) {
                $result = 1;
            }
        } else {
            if ($cookie) {
                $result = 1;
            }
        }
        
        return $result;
    }
    
    /**
     * Returns hashed string.
     *
     * @param string $str
     * @return string
     */
    public function encryptData($str)
    {
        return hash('sha256', $str);
    }
    
    /**
     * Returns cleaned URL.
     *
     * @param string $url
     * @return string
     */
    public function cleanUrl($url)
    {
        $queryPosition = strpos($url, '?');
        
        // If there is a query string remove it
        if ($queryPosition !== false) {
            $url = substr($url, 0, $queryPosition);
        }
        
        return $url;
    }
    
    /**
     * Returns base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->cleanUrl(
            $this->storeManager->getStore()->getBaseUrl()
        );
    }
    
    /**
     * Returns current URL
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->cleanUrl(
            $this->storeManager->getStore()->getCurrentUrl()
        );
    }
    
    /**
     * Returns referer URL
     *
     * @return string
     */
    public function getRefererUrl()
    {
        return $this->cleanUrl(
            $this->redirect->getRefererUrl()
        );
    }
    
    /**
     * Generates Event ID used for deduplication.
     *
     * @param string $eventName
     * @param string $entityId
     * @param string $entityType
     * @param boolean $withWrapper
     * @param boolean $useBaseUrl
     * @return string|array
     */
    public function generateEventId(
        $eventName,
        $entityId,
        $entityType = 'cms',
        $withWrapper = false,
        $useBaseUrl = false
    ) {
        $id = '';
        
        $storeId = $this->getStoreId();
        $ip      = $this->getClientIpAddress();
        $ua      = $this->getClientUserAgent();
        
        if ($useBaseUrl) {
            $url = $this->getBaseUrl();
        } else {
            $url = $this->getCurrentUrl();
        }
        
        $currentCurrency = $this->getCurrentCurrencyCode();
        
        $fbp = $this->getFbp();
        
        $id = $eventName . '.' . $entityId . '.' . $entityType . '.' . $storeId . '.'
            . $ip . '.' . $ua . '.' . $url . '.' . $currentCurrency . '.' . $fbp . '.' . time();
        
        // Encrypt the event ID
        $id = $this->encryptData($id);
        
        // With wrapper means with array aorund the event ID
        // That is used in .phtml file
        if ($withWrapper) {
            $eventId = [];
            $eventId['eventID'] = $id;
            return $eventId;
        } else {
            return $id;
        }
    }
    
    /**
     * Returnes data for server AddToCart or AddTo Wishlist event.
     *
     * @param int $productId
     * @param string $eventName
     * @return array
     */
    public function getDataForServerAddToCartOrWishlistEvent($productId, $eventName = 'AddToCart')
    {
        $data = [];
        
        $userData    = $this->getUserDataForServer();
        $productData = $this->getProductData($productId);
        $customData  = $productData['data'];
        
        $data['ready_data']['data'][0]['event_name']  = $eventName;
        $data['ready_data']['data'][0]['event_time']  = time();
        $data['ready_data']['data'][0]['event_id']    = $this
            ->generateEventId($eventName, $productId, 'product', false, true);
        
        // Optional
        $data['ready_data']['data'][0]['event_source_url'] = $this->getRefererUrl();
        
        $data['ready_data']['data'][0]['user_data']   = $userData;
        $data['ready_data']['data'][0]['custom_data'] = $customData;
        
        // Optional
        $data['ready_data']['data'][0]['opt_out'] = false;
        
        $data['additional_data'] = $productData['contents_with_ids'];
        
        return $data;
    }
    
    /**
     * Returns data for client CompleteRegistration event.
     *
     * @param number $customerId
     * @return array
     */
    public function getDataForClientCompleteRegistrationEvent($customerId = 0)
    {
        if (!$this->isEventEnabled('CompleteRegistration')) {
            return [];
        }
        
        $d    = [];
        $data = [];
        
        $eventName = 'CompleteRegistration';
        
        $data['content_name'] = 'New Customer Registration';
        $data['status']       = true;
        $data['value']        = 0;
        $data['currency']     = $this->getCurrentCurrencyCode();
        
        $d['data']       = $data;
        $d['event_name'] = $eventName;
        $d['event_id']   = $this->generateEventId($eventName, 1, 'customer', true);
        
        return $d;
    }
    
    /**
     * Returns data for server CompleteRegistration event.
     *
     * @param int $customerId
     * @return array
     */
    public function getDataForServerCompleteRegistrationEvent($customerId = 0)
    {
        $d = [];
        
        $i = 0;
        
        if ($this->isEventEnabled('CompleteRegistration', true)) {
            $customData = [];
            
            $eventName = 'CompleteRegistration';
            $userData  = $this->getUserDataForServer($customerId);
            
            $customData['content_name'] = 'New Customer Registration';
            $customData['status']       = true;
            $customData['value']        = 0;
            $customData['currency']     = $this->getCurrentCurrencyCode();
            
            $d['data'][0]['event_name'] = $eventName;
            $d['data'][0]['event_time'] = time();
            $d['data'][0]['event_id']   = $this
                ->generateEventId($eventName, 1, 'customer');
            
            // Optional
            $d['data'][0]['event_source_url'] = $this->getCurrentUrl();
            
            $d['data'][0]['user_data']   = $userData;
            $d['data'][0]['custom_data'] = $customData;
            
            // Optional
            $d['data'][0]['opt_out'] = false;
            
            $i = 1;
        }
        
        // Add PageView based on config
        $isPageViewEnabled = $this->isEventEnabled('PageView', true);
        $isPageViewWithAll = $this->isPageViewWithAll(true);
        
        if ($isPageViewEnabled && $isPageViewWithAll) {
            $pageViewEvent = $this->getDataForServerPageViewEvent($customerId);
            
            if ($i) {
                $pageViewData  = $pageViewEvent['data'][0];
                $d['data'][$i] = $pageViewData;
            } else {
                $d = $pageViewEvent;
            }
        }
        
        return $d;
    }
    
    /**
     * Fires server event via curl and returns response JSON string.
     *
     * @param array $data
     * @return string
     */
    public function fireServerEvent($data)
    {
        $pixelIds       = $this->getFacebookPixelId();
        $accessTokens   = $this->getAccessToken();
        $apiVersion     = $this->getApiVersion();
        $testEventCode  = $this->getTestEventCode();
        $logEvents      = $this->isLogEventsEnabled();
        $url            = '';
        $i              = 0;
        $k              = 0;
        $responseData   = [];
        
        // If cookie consent feature is enabled and consent is not granted
        if ($this->isCookieConsentEnabled() && !$this->isConsentGranted()) {
            return $responseData;
        }
        
        if (!empty($accessTokens) && $apiVersion && !empty($pixelIds) && array_key_exists('data', $data)) {
            if ($testEventCode) {
                $data['test_event_code'] = $testEventCode;
            }
            
            $data['partner_agent'] = 'dvapptrian';
            
            if ($this->isMoveParamsOutsideContentsEnabled(true)) {
                // Move parameters outside contents
                $data = $this->moveParamsOutsideContentsForServer($data);
            }
            
            $eventCount = count($data['data']);
            
            for ($i = 0; $i < $eventCount; $i++) {
                // New action_source field
                $data['data'][$i]['action_source'] = 'website';
                
                // California Consumer Privacy Act (CCPA)
                // Limited Data Use (LDU)
                if ($this->isDataProcessingEnabled()) {
                    $data['data'][$i]['data_processing_options']         = $this->getDpo();
                    $data['data'][$i]['data_processing_options_country'] = $this->getDpoCountry();
                    $data['data'][$i]['data_processing_options_state']   = $this->getDpoState();
                }
            }
            
            $pixelCount = count($pixelIds);
            $tokenCount = count($accessTokens);
            
            if ($pixelCount != $tokenCount) {
                $this->logger->critical(
                    'Pixel Id count does not match Access Token count. Check your settings: 
                    1. Stores > Configuration > Apptrian Extensions > Facebook Pixel and Conversions API > 
                    Facebook Pixel (Client-Side Settings) > Facebook Pixel ID 
                    2. Stores > Configuration > Apptrian Extensions > Facebook Pixel and Conversions API > 
                    Facebook Conversions API (Server-Side Settings) > Facebook Access Token'
                );
            }
            
            // There maybe multiple pixels so you send multiple requests
            for ($k = 0; $k < $pixelCount; $k++) {
                $url = 'https://graph.facebook.com/' . $apiVersion . '/' . $pixelIds[$k] . '/events';
                $data['access_token'] = $accessTokens[$k];
                
                try {
                    $this->curl->post($url, $data);
                    $response = $this->curl->getBody();
                    $responseData = $response;
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
                
                if ($logEvents) {
                    $this->logger->critical('Event Data');
                    $this->logger->critical(json_encode($data, JSON_PRETTY_PRINT));
                    $this->logger->critical('Event Response');
                    $this->logger->critical(json_encode($responseData, JSON_PRETTY_PRINT));
                }
            }
        }
        
        return $responseData;
    }
    
    /**
     * Moves params out of contents for client data. If contents has multiple items it will remove params.
     *
     * @param array $data
     * @return array
     */
    public function moveParamsOutsideContentsForClient($data)
    {
        if (!array_key_exists('contents', $data)) {
            return $data;
        }
        
        $contents = $data['contents'];
        if (count($contents) > 1) {
            $i = 0;
            foreach ($contents as $item) {
                foreach ($item as $param => $value) {
                    if ($param == 'id' || $param == 'item_price' || $param == 'quantity') {
                        continue;
                    }
                    
                    // You cannot do anything with param just
                    // Remove the param from contents
                    unset($data['contents'][$i][$param]);
                }
                
                $i++;
            }
        } else {
            if (empty($contents)) {
                return $data;
            }
            
            $item = $contents[0];
            foreach ($item as $param => $value) {
                if ($param == 'id' || $param == 'item_price' || $param == 'quantity') {
                    continue;
                }
                
                // Set the param
                $data[$param] = $value;
                // Remove the param from contents
                unset($data['contents'][0][$param]);
            }
        }
        
        return $data;
    }
    
    /**
     * Moves params out of contents for server data. If contents has multiple items it will remove params.
     *
     * @param array $data
     * @return array
     */
    public function moveParamsOutsideContentsForServer($data)
    {
        $customData = $data['data'][0]['custom_data'];
        if (!array_key_exists('contents', $customData)) {
            return $data;
        }
        
        $contents = $data['data'][0]['custom_data']['contents'];
        if (count($contents) > 1) {
            $i = 0;
            foreach ($contents as $item) {
                foreach ($item as $param => $value) {
                    if ($param == 'id' || $param == 'item_price' || $param == 'quantity') {
                        continue;
                    }
                    
                    // You cannot do anything with param just
                    // Remove the param from contents
                    unset($data['data'][0]['custom_data']['contents'][$i][$param]);
                }
                
                $i++;
            }
        } else {
            if (empty($contents)) {
                return $data;
            }
            
            $item = $contents[0];
            foreach ($item as $param => $value) {
                if ($param == 'id' || $param == 'item_price' || $param == 'quantity') {
                    continue;
                }
                
                // Set the param
                $data['data'][0]['custom_data'][$param] = $value;
                // Remove the param from contents
                unset($data['data'][0]['custom_data']['contents'][0][$param]);
            }
        }
        
        return $data;
    }
    
    /**
     * Retruns flag for Data Processing Options.
     * (The 1 for enabled and 0 for disabled.)
     *
     * @return int
     */
    public function isDataProcessingEnabled()
    {
        return (int) $this->getConfig(
            'apptrian_facebookpixel/data_processing_options/enabled',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns array of Data Processing Options.
     *
     * @return array
     */
    public function getDpo()
    {
        $dpo = $this->getConfig(
            'apptrian_facebookpixel/data_processing_options/dpo',
            $this->getStoreId()
        );
        
        $dpoArray = [];
        
        if ($dpo) {
            $dpoArray = explode(',', (string) $dpo);
        }
    
        return $dpoArray;
    }
    
    /**
     * Retruns country id for Data Processing Options.
     *
     * @return int
     */
    public function getDpoCountry()
    {
        return (int) $this->getConfig(
            'apptrian_facebookpixel/data_processing_options/dpo_country',
            $this->getStoreId()
        );
    }
    
    /**
     * Retruns state id for Data Processing Options.
     *
     * @return int
     */
    public function getDpoState()
    {
        return (int) $this->getConfig(
            'apptrian_facebookpixel/data_processing_options/dpo_state',
            $this->getStoreId()
        );
    }
    
    /**
     * Returns data for server PageView event.
     *
     * @param int $customerId
     * @return array
     */
    public function getDataForServerPageViewEvent($customerId = 0)
    {
        if (!$this->isEventEnabled('PageView', true)) {
            return [];
        }
        
        $d = [];
        
        $eventName  = 'PageView';
        $userData   = $this->getUserDataForServer($customerId);
        $currentUrl = $this->getCurrentUrl();
        $customData = [];
        
        $d['data'][0]['event_name'] = $eventName;
        $d['data'][0]['event_time'] = time();
        $d['data'][0]['event_id']   = $this
            ->generateEventId($eventName, $currentUrl, 'cms');
        
        // Optional
        $d['data'][0]['event_source_url'] = $currentUrl;
        
        $d['data'][0]['user_data']   = $userData;
        $d['data'][0]['custom_data'] = $customData;
        
        // Optional
        $d['data'][0]['opt_out'] = false;
        
        return $d;
    }
    
    /**
     * Returns user data for Conversions API when fired with AJAX.
     *
     * @param array $data
     * @param string $url
     * @return array
     */
    public function getUserDataForApi($data, $url = '')
    {
        $d = [];
        
        $fbc = $this->getFbc($url);
        $fbp = $this->getFbp();
        
        $d['client_ip_address'] = $this->getClientIpAddress();
        $d['client_user_agent'] = $this->getClientUserAgent();
        
        if ($fbc) {
            $d['fbc'] = $fbc;
        }
        
        if ($fbp) {
            $d['fbp'] = $fbp;
        }
        
        foreach ($data as $key => $value) {
            if ($value) {
                // For Server-Side API data must be encrypted
                $d[$key] = $this->encryptData($this->formatData($value));
            }
        }
        
        return $d;
    }
    
    /**
     * Returns event data for Conversions API when fired with AJAX.
     *
     * @param array $data
     * @return array
     */
    public function getEventDataForApi($data)
    {
        $eventName = '';
        $eventId   = '';
        $url       = '';
        $userData  = [];
        $eventData = [];
        
        $apiData   = [];
        
        if (array_key_exists('eventName', $data)) {
            $eventName = $data['eventName'];
            
            if (!$this->isValidEventName($eventName)) {
                return [];
            }
        }
        
        if (array_key_exists('eventId', $data)) {
            $eventId = $data['eventId'];
            
            if (!$this->isValidEventId($eventId)) {
                return [];
            }
        }
        
        if (array_key_exists('url', $data)) {
            $url = $data['url'];
            
            if (!$this->isValidUrl($url)) {
                return [];
            }
        }
        
        if (array_key_exists('userData', $data)) {
            $userData = $data['userData'];
            
            if (!$this->isValidUserData($userData)) {
                return [];
            }
        }
        
        if (array_key_exists('eventData', $data)) {
            $eventData = $data['eventData'];
            
            if (!$this->isValidEventData($eventData)) {
                return [];
            }
        }
        
        // Add user data params
        $userData = $this->getUserDataForApi($userData, $url);
        
        $apiData['data'][0]['event_name'] = $eventName;
        $apiData['data'][0]['event_id']   = $eventId;
        $apiData['data'][0]['event_time'] = time();
        
        $apiData['data'][0]['event_source_url'] = $url;
        
        $apiData['data'][0]['user_data']   = $userData;
        $apiData['data'][0]['custom_data'] = $eventData;
        
        // Optional
        $apiData['data'][0]['opt_out'] = false;
        
        return $apiData;
    }
    
    /**
     * Check if data is a valid event name.
     *
     * @param array|string $data
     * @return boolean
     */
    public function isValidEventName($data)
    {
        $result = false;
        
        $validEvents = [
            'AddToCart',
            'AddToWhishlist',
            'CompleteRegistration',
            'InitiateCheckout',
            'PageView',
            'Purchase',
            'Search',
            'ViewContent'
        ];
        
        $categoryEventName = $this->getCategoryEventName();
        $searchEventName   = $this->getSearchEventName();
        
        $validEvents[] = $categoryEventName;
        $validEvents[] = $searchEventName;
        
        if (in_array($data, $validEvents)) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Check if data is a valid event ID.
     *
     * @param array|string $data
     * @return boolean
     */
    public function isValidEventId($data)
    {
        return ! preg_match('/[^a-z\d_\-]/i', $data);
    }
    
    /**
     * Check if data is a valid url.
     *
     * @param string $data
     * @return boolean
     */
    public function isValidUrl($data)
    {
        $result = false;
        
        if (is_string($data)) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Check if data is a valid user data array.
     *
     * @param array $data
     * @return boolean
     */
    public function isValidUserData($data)
    {
        $result = false;
        
        if (is_array($data)) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Check if data is a valid event data array.
     *
     * @param array $data
     * @return boolean
     */
    public function isValidEventData($data)
    {
        $result = false;
        
        if (is_array($data)) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Returns Facebook Product Identifier from config.
     *
     * @return string
     */
    public function getIdent()
    {
        if (null === $this->ident) {
            $this->ident = $this->getConfig(
                'apptrian_facebookpixel/miscellaneous/product_identifier',
                $this->getStoreId()
            );
        }
        
        return $this->ident;
    }
    
    /**
     * Retruns predefined array of data keys for Advanced Matching.
     *
     * @return array
     */
    public function getAllowedMatchingKeys()
    {
        return $this->allowedMatchingKeys;
    }
    
    /**
     * Returns Advanced Matching map based on config.
     *
     * @return array
     */
    public function getMatchingParamsMap()
    {
        if (!empty($this->matchingParamsMap)) {
            return $this->matchingParamsMap;
        }
        
        $map = [];
        
        $data = $this->getConfig(
            'apptrian_facebookpixel/miscellaneous/matching',
            $this->getStoreId()
        );
        
        if (!$data) {
            return $map;
        }
        
        $allowedKeys = $this->getAllowedMatchingKeys();
        
        $pairs = explode('|', (string) $data);
        
        foreach ($pairs as $pair) {
            $pairArray = explode('=', (string) $pair);
            
            if (isset($pairArray[0]) && isset($pairArray[1])) {
                $cleanedParameter = trim((string) $pairArray[0]);
                $cleanedKey = trim((string) $pairArray[1]);
                
                if ($cleanedParameter && $cleanedKey && in_array($cleanedKey, $allowedKeys)) {
                    $map[$cleanedKey] = $cleanedParameter;
                }
            }
        }
        
        $this->matchingParamsMap = $map;
        
        return $map;
    }
}
