<?php
namespace NitroPack\NitroPack\Api;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Cart;

use \NitroPack\SDK\NitroPack; // Loaded through SDK autoloader


class NitroService implements NitroServiceInterface {

	const EXTENSION_VERSION = '2.0.0';

	protected static $pageRoutes = array(
		// (full action name) => 'settingName'
		'cms_index_index'            => 'home',
		'catalog_product_view'       => 'product',
		'catalog_category_view'      => 'category',
		'cms_page_view'              => 'info',
		'contact_index_index'        => 'contact'
	);

	protected $connected = false;
	protected $settings = null;
	protected $sdk = null;
	protected $loadedStoreCode = null;

	protected $storeManager;
	protected $currentStoreId;
	protected $appState;
	protected $objectManager;
	protected $directoryList;
	// ! Do not rely on dependency injection for the session and cart, as they may trigger our frontend observers, and we do not want to do that from the constructor, since the observers inject this service, which then would lead to a dependency loop
	protected $session;
	protected $cart;

	public function __construct(
		ObjectManagerInterface $objectManager,
		State $appState,
		DirectoryList $directoryList
	) {
		$this->objectManager = $objectManager;
		$this->appState = $appState;
		$this->directoryList = $directoryList;

		$this->storeManager = $this->objectManager->get(StoreManagerInterface::class);

		$this->loadedStoreCode = null;

		if (!$this->readSettings($this->loadedStoreCode)) {
			$this->settings = static::defaultSettings();
		} else {
			$this->sdk = $this->initializeSdk();
			if ($this->settings->compression) {
				$this->sdk->enableCompression();
			} else {
				$this->sdk->disableCompression();
			}
		}
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::extensionVersion
	 */
	public function extensionVersion() {
		return NitroService::EXTENSION_VERSION;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::sdkVersion
	 */
	public function sdkVersion() {
		return NitroPack::VERSION;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::reload
	 */
	public function reload($storeViewCode=null, $url=null) {
		if (!$this->readSettings($storeViewCode)) {
			$this->settings = static::defaultSettings();
		} else {
			$this->loadedStoreCode = $storeViewCode ? $storeViewCode : $this->loadedStoreCode;
			$this->sdk = $this->initializeSdk($url);
			if ($this->settings->compression) {
				$this->sdk->enableCompression();
			} else {
				$this->sdk->disableCompression();
			}
		}
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::disconnect
	 */
	public function disconnect($storeViewCode=null) {
		$settingsFilename = $this->getSettingsFilename($storeViewCode);
		if (file_exists($settingsFilename) && is_writable($settingsFilename)) {
			unlink($settingsFilename);
		}
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::isConnected
	 */
	public function isConnected() {
		return (
			file_exists($this->getSettingsFilename($this->loadedStoreCode)) &&
			isset($this->settings->siteId) && !empty($this->settings->siteId) &&
			isset($this->settings->siteSecret) && !empty($this->settings->siteSecret)
		);
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::isEnabled
	 */
	public function isEnabled() {
		return $this->settings->enabled;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::getSettings
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::setWarmupSettings
	 */
	public function setWarmupSettings($enabled=null, $pageTypes=null, $currencies=null) {
		$this->settings->cacheWarmup = ($enabled !== null) ? $enabled : $this->settings->cacheWarmup;
		$this->settings->warmupTypes = ($pageTypes !== null) ? $pageTypes : $this->settings->warmupTypes;
		$this->settings->warmupCurrencyVariations = ($currencies !== null) ? $currencies : $this->settings->warmupCurrencyVariations;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::getSettings
	 */
	public function getBuiltInPageTypeRoutes() {
		$inverted = array();
		foreach (static::$pageRoutes as $route => $nitroName) {
			$inverted[$nitroName] = $route;
		}
		return $inverted;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::getSiteId
	 */
	public function getSiteId() {
		return $this->settings->siteId;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::getSiteSecret
	 */
	public function getSiteSecret() {
		return $this->settings->siteSecret;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::getSdk
	 */
	public function getSdk() {
		return $this->sdk;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::setSiteId
	 */
	public function setSiteId($newSiteId) {
		$this->settings->siteId = $newSiteId;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::setSiteSecret
	 */
	public function setSiteSecret($newSiteSecret) {
		$this->settings->siteSecret = $newSiteSecret;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::persistSettings
	 */
	public function persistSettings($storeName=null) {
		if ($storeName == null) {
			$storeName = $this->loadedStoreCode;
		}
		$settingsFilename = $this->getSettingsFilename($storeName);
		if (file_exists($settingsFilename)) {
			if (!is_writable($settingsFilename)) {
				// settings file exists but we cannot write to it
				return false;
			}
		} elseif (!is_writable(dirname($settingsFilename))) {
			// settings file does not exist and we cannot write to its directory
			return false;
		}

		file_put_contents($settingsFilename, json_encode($this->settings));
		return true;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::isCacheable
	 */
	public function isCacheable() {
		if (!$this->cart) {
			$this->cart = $this->objectManager->get(Cart::class);
		}
		if (!empty($this->cart->getQuote()->getAllItems())) {
			return false;
		}

		if (!$this->session) {
			$this->session = $this->objectManager->get(Session::class);
		}
		if ($this->session->isLoggedIn()) {
			return false;
		}

		return true;
	}

	/**
	 * \NitroPack\NitroPack\Api\NitroServiceInterface::isCachableRoute
	 */
	public function isCachableRoute($route) {
		if (isset(static::$pageRoutes[$route]) && $this->settings->pageTypes->{static::$pageRoutes[$route]}) {
			return true;
		}

		return (in_array($route, $this->settings->pageTypes->custom));
	}

	public static function isANitroRequest() {
		return isset($_SERVER['HTTP_X_NITROPACK_REQUEST']);
	}

	// Forward to the SDK object
	public function __call($method, $args) {
		if (!$this->sdk || !method_exists($this->sdk, $method)) {
			throw new \BadMethodCallException('Trying to call nonexistant method ' . $method . ' on an object of type ' . get_called_class());
		}
		return call_user_func_array(array($this->sdk, $method), $args);
	}

	// Forward to the SDK object
	public function __get($key) {
		if (!$this->sdk || !isset($this->sdk->{$key})) {
			trigger_error(sprintf('Undefined member variable %s', $key), E_USER_NOTICE);
			return null;
		}
		return $this->sdk->{$key};
	}

	// Forward to the SDK object
	public function __isset($key) {
		return ($this->sdk && isset($this->sdk->{$key}));
	}

	protected static function defaultSettings(&$target=null, $skipCredentials=false) {
		if ($target == null) {
			$settings = new \stdClass();
		} else {
			$settings = $target;
		}

		$settings->enabled = true;
		$settings->compression = false;

		if (!$skipCredentials) {
			$settings->siteId = null;
			$settings->siteSecret = null;
		}

		if (!isset($settings->autoClear)) {
			$settings->autoClear = new \stdClass();
		}

		$settings->autoClear->products = true;
		$settings->autoClear->attributes = true;
		$settings->autoClear->attributeSets = true;
		$settings->autoClear->reviews = true;
		$settings->autoClear->categories = true;
		$settings->autoClear->pages = true;
		$settings->autoClear->blocks = true;
		$settings->autoClear->widgets = true;
		$settings->autoClear->orders = true;

		if (!isset($settings->pageTypes)) {
			$settings->pageTypes = new \stdClass();
		}

		$settings->pageTypes->home = true;
		$settings->pageTypes->product = true;
		$settings->pageTypes->category = true;
		$settings->pageTypes->info = true;
		$settings->pageTypes->contact = true;

		$settings->pageTypes->custom = array();

		$settings->cacheWarmup = false;

		if (!isset($settings->warmupTypes)) {
			$settings->warmupTypes = new \stdClass;
		}

		$settings->warmupTypes->home = true;
		$settings->warmupTypes->product = true;
		$settings->warmupTypes->category = true;
		$settings->warmupTypes->info = true;
		$settings->warmupTypes->contact = true;

		if (!isset($settings->warmupPriority)) {
			$settings->warmupPriority = new \stdClass;
		}

		$settings->warmupPriority->home          = 1.0;
		$settings->warmupPriority->info          = 0.9;
		$settings->warmupPriority->contact       = 0.8;
		$settings->warmupPriority->category      = 0.7;
		$settings->warmupPriority->product       = 0.6;

		if (!isset($settings->warmupCurrencyVariations) || !is_array($settings->warmupCurrencyVariations)) {
			$settings->warmupCurrencyVariations = array();
		}

		return $settings;
	}

	protected function readSettings($storeName=null) {
		$settingsFilename = $this->getSettingsFilename($storeName);
		if (file_exists($settingsFilename) && is_readable($settingsFilename)) {
			$contents = @file_get_contents($settingsFilename);

			if (!$contents) {
				return false;
			}

			$this->settings = @json_decode($contents);

			if ($this->settings) {
				return true;
			}
		}
		return false;
	}

	protected function getSettingsFilename($storeName=null) {
		$rootPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
		
		try {
			$rootPath = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR;
		} catch (\Magento\Framework\Exception\FileSystemException $e) {
			// fallback to using the module directory
		}
	
		if ($storeName === null) {
			// check if in admin or frontend
			$area = $this->appState->getAreaCode();

			if ($area == Area::AREA_FRONTEND) {
				if (!$this->storeManager) $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
				$storeName = $this->storeManager->getStore()->getCode();
			} elseif ($area == Area::AREA_ADMINHTML) {
				return $rootPath . 'nitro_settings_NO_STORE.json';
			}
		}
		return $rootPath . 'nitro_settings_' . $storeName . '.json';
	}

	protected function initializeSdk($url = null) {
		if (!$this->settings->siteId || !$this->settings->siteSecret) {
			return null;
		}

		$rootPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
		
		try {
			$rootPath = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR;
		} catch (\Magento\Framework\Exception\FileSystemException $e) {
			// fallback to using the module directory
		}

		$cachePath = $rootPath . 'nitro_cache' . DIRECTORY_SEPARATOR . $this->settings->siteId;

		if (!file_exists($cachePath)) {
			mkdir($cachePath, 0777, true);
		}

		return new NitroPack($this->settings->siteId, $this->settings->siteSecret, null, $url, $cachePath);
	}

	private function getUrl() {
		return $this->getScheme() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	private function getScheme() {
		return $this->isSecure() ? 'https://' : 'http://';
	}

	private function isSecure() {
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
	}

}
