<?php
namespace NitroPack\NitroPack\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;

use NitroPack\NitroPack\Api\NitroServiceInterface;
use NitroPack\NitroPack\Api\CacheWarmupInterface;

class SettingsBlock extends Template {

	protected static $cachesToDisable = ['full_page', 'layout', 'block_html'];

	protected $nitro;
	protected $cacheWarmup;

	protected $store;
	protected $_backendUrl;
	protected $_storeManager;
	protected $_scopeConfig;
	protected $_cacheState;
	protected $_cacheTypeList;

	public function __construct(
		Context $context, // required as part of the Magento\Backend\Block\Template constructor
		NitroServiceInterface $nitro, // dependency injection'ed
		CacheWarmupInterface $cacheWarmup, // dependency injection'ed
		UrlInterface $backendUrl, // dependency injection'ed
		StoreManagerInterface $storeManager, // dependency injection'ed
		RequestInterface $request, // dependency injection'ed
		ScopeConfigInterface $scopeConfig, // dependency injection'ed
		StateInterface $cacheState, // dependency injection'ed
		TypeListInterface $cacheTypeList, // dependency injection'ed
		array $data = [] // required as part of the Magento\Backend\Block\Template constructor
	) {
		parent::__construct($context, $data);
		$this->nitro = $nitro;
		$this->cacheWarmup = $cacheWarmup;
		$this->_backendUrl = $backendUrl;
		$this->_storeManager = $storeManager;
		$this->_scopeConfig = $scopeConfig;
		$this->_cacheState = $cacheState;
		$this->_cacheTypeList = $cacheTypeList;
	}

	public function getSettings() {
		return $this->nitro->getSettings();
	}

	protected function getBackendUrl($route, $withStore=true, $withFormKey=false) {
		$params = array();
		if ($withStore) $params['store'] = $this->getStore()->getId();
		if ($withFormKey) $params['form_key'] = $this->getFormKey();
		return $this->_backendUrl->getUrl($route, $params);
	}

	public function getSaveUrl() {
		return $this->getBackendUrl('NitroPack/settings/save');
	}

	public function getIntegrationUrl($integration) {
		return $this->nitro->integrationUrl($integration);
	}

	public function getDisconnectUrl() {
		return $this->getBackendUrl('NitroPack/settings/disconnect', true, true);
	}

	public function getConnectUrl() {
		return $this->getBackendUrl('NitroPack/connect/index', true, false);
	}

	public function getCacheWarmupSaveUrl() {
		return $this->getBackendUrl('NitroPack/warmup/save', false, true); // not store specific
	}

	public function getStartWarmupUrl() {
		return $this->getBackendUrl('NitroPack/warmup/start', true, true);
	}

	public function getPauseWarmupUrl() {
		return $this->getBackendUrl('NitroPack/warmup/pause', true, true);
	}

	public function getDisableCachesUrl() {
		return $this->getBackendUrl('NitroPack/settings/disablecaches', true, true);
	}

	public function getCacheManagementUrl() {
		// route the magento System > Tools > Cache management page
		return $this->getBackendUrl('adminhtml/cache/index', false, false);
	}

	public function getStore() {
		if (!$this->store) {
			$storeId = (int) $this->_request->getParam('store', 0);
			if ($storeId == 0) {
				$storeId = $this->_storeManager->getDefaultStoreView()->getId();
			}
			$store = $this->_storeManager->getStore($storeId);
			$this->store = $store;
		}
		return $this->store;
	}

	public function getAvailableCurrencies() {
		return $this->_storeManager->getStore()->getAvailableCurrencyCodes();
	}

	public function getAvailableLocales() {
		$locales = array();

		$currentStore = $this->_storeManager->getStore();
		$currentGroupId = $currentStore->getStoreGroupId();

		$stores = $this->_storeManager->getStores();
		foreach ($stores as $store) {
			if ($store->getStoreGroupId() != $currentGroupId) continue;
			$locales[] = $this->_scopeConfig->getValue(
				'general/locale/code',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$store->getId()
			);
		}

		return $locales;
	}

	public function getBuiltInPageTypeRoutes() {
		return $this->nitro->getBuiltInPageTypeRoutes();
	}

	public function getWarmupStats() {
		$stats = $this->nitro->getApi()->getWarmupStats();
		$stats['is_warmup_active'] = (bool)$stats['status'] && (bool)$stats['pending'];
		return $stats;
	}

	public function getWarmupConfig() {
		return $this->cacheWarmup->getConfig();
	}

	public function getEnabledCaches() {
		$caches = array();
		foreach (static::$cachesToDisable as $code) {
			if ($this->_cacheState->isEnabled($code)) {
				$caches[] = $code;
			}
		}
		return $caches;
	}

	public function getCacheLabels() {
		return $this->_cacheTypeList->getTypeLabels();
	}

}
