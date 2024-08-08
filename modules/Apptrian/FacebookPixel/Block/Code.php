<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */

namespace Apptrian\FacebookPixel\Block;

class Code extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Apptrian\FacebookPixel\Helper\Data
     */
    public $helper;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Apptrian\FacebookPixel\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Apptrian\FacebookPixel\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        
        parent::__construct($context, $data);
    }
    
    /**
     * Used in .phtml file and returns array of data.
     *
     * @return array
     */
    public function getFacebookPixelData()
    {
        $data = [];
        
        $data['id_data']               = $this->helper->getFacebookPixelId();
        $data['full_action_name']      = $this->getRequest()->getFullActionName();
        $data['page_handles']          = $this->helper->getPageHandles();
        $data['page_handles_category'] = $this->helper->getPageHandles('category');
        $data['page_handles_product']  = $this->helper->getPageHandles('product');
        $data['page_handles_quote']    = $this->helper->getPageHandles('quote');
        $data['page_handles_order']    = $this->helper->getPageHandles('order');
        $data['page_handles_search']   = $this->helper->getPageHandles('search');
    
        return $data;
    }
    
    /**
     * Returns configuration value for Facebook Pixel.
     *
     * @return bool
     */
    public function isPixelEnabled()
    {
        return $this->helper->isPixelEnabled();
    }
    
    /**
     * Returns configuration value for base_code_enabled.
     *
     * @return bool
     */
    public function isBaseCodeEnabled()
    {
        return $this->helper->isBaseCodeEnabled();
    }
    
    /**
     * Returns configuration value for noscript_enabled.
     *
     * @return bool
     */
    public function isNoScriptEnabled()
    {
        return $this->helper->isNoScriptEnabled();
    }
    
    /**
     * Returns configuration value for Facebook Conversions API.
     *
     * @return bool
     */
    public function isApiEnabled()
    {
        return $this->helper->isApiEnabled();
    }
    
    /**
     * Returns configuration value for firing mode for Facebook Conversions API.
     *
     * @return bool
     */
    public function getFiringMode()
    {
        return $this->helper->getFiringMode();
    }
    
    /**
     * Returns configuration value for PageView with all.
     *
     * @param bool $server
     * @return int
     */
    public function isPageViewWithAll($server = false)
    {
        return $this->helper->isPageViewWithAll($server);
    }
    
    /**
     * Returns category data needed for tracking.
     *
     * @return array
     */
    public function getCategoryData()
    {
        return $this->helper->getCategoryDataForClient();
    }
    
    /**
     * Returns product data needed for tracking.
     *
     * @param int $id
     * @return array
     */
    public function getProductData($id = 0)
    {
        return $this->helper->getProductData($id);
    }
    
    /**
     * Returns data needed for tracking from order object.
     *
     * @return array
     */
    public function getOrderData()
    {
        return $this->helper->getOrderDataForClient();
    }
    
    /**
     * Returns data needed for tracking from quote object.
     *
     * @return array
     */
    public function getQuoteData()
    {
        return $this->helper->getQuoteDataForClient();
    }
    
    /**
     * Returns search data needed for tracking.
     *
     * @return array
     */
    public function getSearchData()
    {
        return $this->helper->getSearchDataForClient();
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
        return $this->helper->isEventEnabled($event, $server);
    }
    
    /**
     * Returns configuration value for noscript_enabled.
     *
     * @param string $event
     * @return bool
     */
    public function isApiEventEnabled($event)
    {
        return $this->helper->isApiEventEnabled($event);
    }
    
    /**
     * Returns configuration value for moving params outside contents.
     *
     * @param bool $server
     * @return int
     */
    public function isMoveParamsOutsideContentsEnabled($server = false)
    {
        return $this->helper->isMoveParamsOutsideContentsEnabled($server);
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
        return $this->helper->isDetectSelectedSkuEnabled($productType, $server);
    }
    
    /**
     * Returns price decimal sign
     *
     * @return string
     */
    public function getPriceDecimalSymbol()
    {
        return $this->helper->getPriceDecimalSymbol();
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
        return $this->helper->getDisplayTaxFlag();
    }
    
    /**
     * Returns data for CompleteRegistration event.
     *
     * @param int $customerId
     * @return array
     */
    public function getDataForCompleteRegistrationEvent($customerId = 0)
    {
        return $this->helper->getDataForClientCompleteRegistrationEvent($customerId);
    }
    
    /**
     * Retruns flag for Data Processing Options.
     * (The 1 for enabled and 0 for disabled.)
     *
     * @return int
     */
    public function isDataProcessingEnabled()
    {
        return $this->helper->isDataProcessingEnabled();
    }
    
    /**
     * Returns array of Data Processing Options.
     *
     * @return array
     */
    public function getDpo()
    {
        return $this->helper->getDpo();
    }
    
    /**
     * Retruns country id for Data Processing Options.
     *
     * @return int
     */
    public function getDpoCountry()
    {
        return $this->helper->getDpoCountry();
    }
    
    /**
     * Retruns state id for Data Processing Options.
     *
     * @return int
     */
    public function getDpoState()
    {
        return $this->helper->getDpoState();
    }
    
    /**
     * Returns category ID marker.
     *
     * @return string
     */
    public function getCategoryIdMarker()
    {
        $currentCategory = $this->helper->currentCategory->getCategory();
        
        if ($currentCategory) {
            return 'var apptrianFacebookPixelCategoryId=' . $currentCategory->getId() . ';';
        }
        
        return '';
    }
    
    /**
     * Returns product ID marker.
     *
     * @return string
     */
    public function getProductIdMarker()
    {
        $currentProduct = $this->helper->currentProduct->getProduct();
        
        if ($currentProduct) {
            return 'var apptrianFacebookPixelProductId=' . $currentProduct->getId() . ';';
        }
        
        return '';
    }
    
    /**
     * Returns search marker.
     *
     * @return string
     */
    public function getSearchMarker()
    {
        $searchHandles = $this->helper->getPageHandles('search');
        $action        = $this->getRequest()->getFullActionName();
        
        if (in_array($action, $searchHandles)) {
            return 'var apptrianFacebookPixelSearch=1;';
        }
        
        return '';
    }
    
    /**
     * Returns url marker.
     *
     * @return string
     */
    public function getUrlMarker()
    {
        // Do not show marker onregistration, quote and order pages
        $regHandle    = 'customer_account_create';
        $quoteHandles = $this->helper->getPageHandles('quote');
        $orderHandles = $this->helper->getPageHandles('order');
        $action       = $this->getRequest()->getFullActionName();
        
        if ($action == $regHandle
            || in_array($action, $quoteHandles)
            || in_array($action, $orderHandles)
        ) {
            return '';
        } else {
            $currentUrl = $this->helper->getCurrentUrl();
            return 'var apptrianFacebookPixelUrl="' . $currentUrl . '";';
        }
    }
    
    /**
     * Returns cookie_consent_enabled from config.
     *
     * @return int
     */
    public function isCookieConsentEnabled()
    {
        return $this->helper->isCookieConsentEnabled();
    }
    
    /**
     * Returns consent_cookie_name from config.
     *
     * @return string
     */
    public function getConsentCookieName()
    {
        return $this->helper->getConsentCookieName();
    }
    
    /**
     * Returns consent_cookie_key from config.
     *
     * @return string
     */
    public function getConsentCookieKey()
    {
        return $this->helper->getConsentCookieKey();
    }
    
    /**
     * Returns consent_cookie_value from config.
     *
     * @return string
     */
    public function getConsentCookieValue()
    {
        return $this->helper->getConsentCookieValue();
    }
    
    /**
     * Returns consent_button from config.
     *
     * @return string
     */
    public function getConsentButton()
    {
        return $this->helper->getConsentButton();
    }
}
