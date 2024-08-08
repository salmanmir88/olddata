<?php
namespace NitroPack\NitroPack\Api;

use Magento\Store\Model\StoreManagerInterface;

use NitroPack\NitroPack\Helper\AdminFrontendUrl;

class CacheWarmup implements CacheWarmupInterface {

	protected $nitro;
	protected $urlHelper;
	protected $storeManager;

	public function __construct(StoreManagerInterface $storeManager, NitroServiceInterface $nitro, AdminFrontendUrl $urlHelper) {
		$this->storeManager = $storeManager;
		$this->nitro = $nitro;
		$this->urlHelper = $urlHelper;
	}

	public function getConfig() {
		$stores = $this->storeManager->getStores(false,true);
		$config = $this->getBlankConfig($stores);

		// update the blank config with the settings from each of the store views
		foreach ($stores as $storeCode => $store) {
			$this->nitro->reload($storeCode);

			$index = 0;
			for (;$index<count($config['storeViews']);++$index) {
				if ($config['storeViews'][$index]['code'] == $store->getCode()) {
					break;
				}
			}

			if ($this->nitro->isConnected()) {
				$config['storeViews'][$index]['connected'] = true;
				$settings = $this->nitro->getSettings();

				if ($settings->cacheWarmup) {
					$config['storeViews'][$index]['enabled'] = true;
				}

				if ($settings->warmupTypes->home)     $config['pageTypes']['home'] = true;
				if ($settings->warmupTypes->product)  $config['pageTypes']['product'] = true;
				if ($settings->warmupTypes->category) $config['pageTypes']['category'] = true;
				if ($settings->warmupTypes->info)     $config['pageTypes']['info'] = true;
				if ($settings->warmupTypes->contact)  $config['pageTypes']['contact'] = true;

				if (!empty($settings->warmupCurrencyVariations)) {
					foreach ($settings->warmupCurrencyVariations as $currency) {
						$config['currencies'][$currency] = true;
					}
				}
			} else {
				continue;
			}
		}

		return $config;
	}

	public function setConfig($newConfig) {
		$stores = $this->storeManager->getStores(false, true);
		$config = $this->getBlankConfig($stores);

		foreach ($newConfig['storeViews'] as $storeView) {
			$index = 0;

			for (;$index<count($config['storeViews']);++$index) {
				if ($config['storeViews'][$index]['code'] == $storeView['code']) {
					break;
				}
			}

			if ($index == count($config['storeViews'])) continue;
			if ($storeView['connected']) $config['storeViews'][$index]['connected'] = true;
			if ($storeView['enabled']) $config['storeViews'][$index]['enabled'] = true;
		}

		foreach (array_keys($config['currencies']) as $currency) {
			if (isset($newConfig['currencies'][$currency]) && $newConfig['currencies'][$currency]) $config['currencies'][$currency] = true;
		}

		foreach (array_keys($config['pageTypes']) as $pageType) {
			if (isset($newConfig['pageTypes'][$pageType]) && $newConfig['pageTypes'][$pageType]) $config['pageTypes'][$pageType] = true;
		}

		$this->persistConfig($config);
	}

	public function getEstimate() {

	}

	// returns a default config object prepared with the available store views and their respective currencies, everything disabled by default
	protected function getBlankConfig($stores, &$currencies=null, &$storeCurrencies=null) {
		if ($currencies == null) {
			$currencies = array();
		}
		if ($storeCurrencies == null) {
			$storeCurrencies = array();
		}
		foreach ($stores as $storeCode => $store) {
			$storeCurrencies[$storeCode] = $store->getAvailableCurrencyCodes();
			$currencies = array_merge($currencies, $storeCurrencies[$storeCode]);
		}
		$currencies = array_unique($currencies);

		$toggleCurrencies = array();
		foreach ($currencies as $currency) {
			$toggleCurrencies[$currency] = false;
		}

		$storeViews = array();
		foreach ($stores as $storeCode => $store) {
			$storeViews[] = array(
				'code' => $store->getCode(),
				'name' => $store->getName(),
				'connected' => false,
				'enabled' => false,
				'currencies' => $storeCurrencies[$store->getCode()]
			);
		}

		return array(
			'storeViews' => $storeViews,
			'currencies' => $toggleCurrencies,
			'pageTypes' => array(
				'home' => false,
				'product' => false,
				'category' => false,
				'info' => false,
				'contact' => false
			)
		);
	}

	// handles updating the variation cookie values in the appropriate connected sites on the NitroPack service side, as well as updating the local settings files to match the config
	protected function persistConfig($config) {
		$nitroSites = array();

		$enabledCurrencies = array();
		foreach ($config['currencies'] as $currency => $status) {
			if ($status) $enabledCurrencies[] = $currency;
		}

		foreach ($config['storeViews'] as $storeView) {
			if ($storeView['connected']) {
				$this->nitro->reload($storeView['code']);
				$siteId = $this->nitro->getSiteId();
				$wasEnabled = $this->nitro->getSettings()->cacheWarmup;

				if (!isset($nitroSites[$siteId])) {
					$nitroSites[$siteId] = array(
						'representativeStore' => $storeView['code'], // we just store one example store connected to this site ID
						'stores' => array(),
						'currencies' => array()
					);
				}

				if ($storeView['enabled']) {
					$nitroSites[$siteId]['stores'][] = $storeView['code'];
				}

				$enabledCurrenciesForStore = array_intersect($enabledCurrencies, $storeView['currencies']);

				if (!empty($enabledCurrenciesForStore)) {
					$nitroSites[$siteId]['currencies'] = array_merge($nitroSites[$siteId]['currencies'], $enabledCurrenciesForStore);
				}

				// handle the local settings update
				$this->nitro->setWarmupSettings($storeView['enabled'], $config['pageTypes'], $enabledCurrenciesForStore);
				$this->nitro->persistSettings();

				if (!$wasEnabled && $storeView['enabled']) {
					$sitemapUrl = $this->getWarmupSitemapUrl();
					$this->nitro->getApi()->setWarmupSitemap($sitemapUrl);
					$this->nitro->getApi()->enableWarmup();
					$this->nitro->getApi()->resetWarmup();
				} elseif ($wasEnabled && !$storeView['enabled']) {
					$this->nitro->getApi()->unsetWarmupSitemap();
					$this->nitro->getApi()->disableWarmup();
					$this->nitro->getApi()->resetWarmup();
				}
			}
		}

		// update the variation cookie on the service side
		foreach ($nitroSites as $siteId => $siteData) {
			$this->nitro->reload($siteData['representativeStore']);
			
			$this->nitro->getApi()->setVariationCookie(NitroCookie::STORE_COOKIE, $siteData['stores'], 1);
			$this->nitro->getApi()->setVariationCookie(NitroCookie::CURRENCY_COOKIE, array_unique($siteData['currencies']), 1);
		}
	}

	protected function getWarmupSitemapUrl() {
		return $this->urlHelper->getUrl('NitroPack/Sitemap/Index');
	}

}