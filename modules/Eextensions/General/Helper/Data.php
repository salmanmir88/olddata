<?php
/**

 */
namespace Eextensions\General\Helper;
 

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;


class Data extends AbstractHelper
{
    /**
   		* @var \Magento\Framework\App\Config\ScopeConfigInterface
   	*/
	protected $scopeConfig;

	protected $session;
	
	protected $_productRepository;

	const XML_PATH_HOME_PAGE_CATEGORY_TAB 		= 'homepagetab/category_tab/selected_category_id';
	

	const XML_PATH_RECAPTCHA_CHECK  	  	= 'msp_securitysuite_recaptcha/frontend/enabled';
	const XML_PATH_RECAPTCHA_CHECK_LOGIN 	= 'msp_securitysuite_recaptcha/frontend/enabled_login';
	const XML_PATH_RECAPTCHA_CHECK_CREATE  	= 'msp_securitysuite_recaptcha/frontend/enabled_create';
	const XML_PATH_RECAPTCHA_WEBSITE 		= 'msp_securitysuite_recaptcha/general/public_key';
	const XML_PATH_RECAPTCHA_SECRET 		= 'msp_securitysuite_recaptcha/general/private_key';

	 

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Catalog\Model\ProductRepository $productRepository,  
		\Magento\Framework\App\Http\Context $httpContext,
		\Magento\Framework\Url\EncoderInterface $encoder,
		UrlInterface $urlInterface
	)
	{
	 	$this->scopeConfig = $scopeConfig;
	 	$this->_productRepository = $productRepository;
        $this->httpContext = $httpContext;
        $this->encoder = $encoder;
		$this->urlInterface = $urlInterface;
        parent::__construct($context);
	}

	public function getDotdot($string,$length)
    {
		$string = (strlen($string) > $length) ? substr($string,0,$length).'...' : $string;
		return $string;
    }

    public function getCurrencySymbol()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
		$currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
		$currencySymbol = $currency->getCurrencySymbol();
		if($currencySymbol){ $symbol = $currencySymbol; }else{ $symbol = $currencyCode; }

		return $symbol;	
    }
    
    public function getSelectedCategoryTabId()
    {
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$selectedCategoryTab =  $this->scopeConfig->getValue(self::XML_PATH_HOME_PAGE_CATEGORY_TAB, $storeScope);
		return $selectedCategoryTab;
    }
	
   
    	public function getProductBySku($sku)
	{	
		return $this->_productRepository->get($sku);
	}
	
	public function getRefUrl($producturl){
		$urnlencode = $this->encoder->encode($producturl);
		$login_url = $this->urlInterface
					->getUrl('customer/account/login', 
						array('referer' => $urnlencode )
					);
		return $login_url;
	} 
	
	/* check item exists in wishlist or not */
    
    public function getCheckWishlist($productId = 0) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_in_wishlist = false;
        foreach ($objectManager->create('Magento\Wishlist\Helper\Data')->getWishlistItemCollection() as $_wishlist_item){
            if($productId == $_wishlist_item->getProduct()->getId()){
                $_in_wishlist = true;
            }
        }
        return $_in_wishlist;
	}

    /* item detail in wishlist  */
    
    public function wishlistItemDetail($productId = 0) {
        $wishlistItem = '';
        $objectManager  =   \Magento\Framework\App\ObjectManager::getInstance();
        $wishlistItems  =   $objectManager->create('Magento\Wishlist\Helper\Data')->getWishlistItemCollection();
        //$wishlistItem   =   $wishlistItems->addFieldToFilter('product_id', $productId);

        foreach ($wishlistItems as $wishlistItem) {
            if ($wishlistItem->getProductId() == $productId) {
                $datadata = $wishlistItem->getData();
            }
        }
        return $wishlistItem;
	}

	public function getGoogleRecaptchaEnable(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$googlerecaptchakeyenable  =  $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_CHECK, $storeScope);
		return $googlerecaptchakeyenable;
	}
    public function getGooglePublicKey(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$googlepublickey  =  $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_WEBSITE, $storeScope);
		return $googlepublickey;
	}
    public function getGooglePrivateKey(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$googleprivatekey  =  $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_SECRET, $storeScope);
		return $googleprivatekey;
	}
	public function getGoogleRecaptchaEnableLogin(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$googlerecaptchakeyenable  =  $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_CHECK_LOGIN, $storeScope);
		return $googlerecaptchakeyenable;
	}
	public function getGoogleRecaptchaEnableCreate(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$googlerecaptchakeyenable  =  $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_CHECK_CREATE, $storeScope);
		return $googlerecaptchakeyenable;
	}
	
	public function allowExtension(){
     return  true;
     return  $this->scopeConfig->getValue('eextensions_seo_config/general/enable', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }
    
      /* Get store information */
      
    public function getStoreName()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/name',$storeScope);
	}
    public function getStoreOpeningHours()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/hours',$storeScope);
	}
	public function getStorePhone()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/phone',$storeScope);
	}  
	public function getStorePostCode()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/postcode',$storeScope);
	}  
	public function getStoreMerchantVatNumber()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/merchant_vat_number',$storeScope);
	}  
	public function getStoreStreetLine1()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/street_line1',$storeScope);
	}  
	public function getStoreStreetLine2()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/street_line2',$storeScope);
	}  
	public function getStoreCity()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/city',$storeScope);
	}  
	public function getStoreCountryId()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/country_id',$storeScope);
	}  
	public function getStoreRegionId()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue('general/store_information/region_id',$storeScope);
	}  
	public function getProductData($pId = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($pId);
		return $product;
    }
}
