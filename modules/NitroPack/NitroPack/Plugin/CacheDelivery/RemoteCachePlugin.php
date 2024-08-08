<?php
namespace NitroPack\NitroPack\Plugin\CacheDelivery;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\Resolver;

use NitroPack\NitroPack\Api\NitroServiceInterface;
use NitroPack\NitroPack\Api\NitroService;
use NitroPack\NitroPack\Api\NitroCookieInterface;
use NitroPack\NitroPack\Observer\CacheTagObserver;

class RemoteCachePlugin {
	// Checks if there is remote cache once the request has been routed, so we know the page type. Called after any RouterInterface instances' match() method.

	protected $nitro = null;
	protected $request = null;
	protected $storeManager = null;
	protected $cookies = null;

	public function __construct(
		NitroServiceInterface $nitro,
		NitroCookieInterface $cookies,
		RequestInterface $request,
		StoreManagerInterface $storeManager,
		Resolver $localeResolver
	) {
		$this->nitro = $nitro;
		$this->request = $request;
		$this->storeManager = $storeManager;
		$this->cookies = $cookies;
		$this->localeResolver = $localeResolver;
	}

	public function afterMatch(\Magento\Framework\App\RouterInterface $subject, $returnValue) {
		if (headers_sent() ||  NitroService::isANitroRequest()) {
			return $returnValue;
		}

		if (!$this->nitro->isConnected() || !$this->nitro->isEnabled()) {
			return $returnValue;
		}

		CacheTagObserver::disableObservers();

		if ($returnValue === null ||
			!$this->nitro->isCacheable() || // Magento specific checks if the request can be cached
			get_class($returnValue) == self::class || // if a router just wraps around another one and calls its match method, we'll get an object with the same class name as a return value
			is_a($returnValue, 'Magento\Framework\App\Action\Forward') || // if a router returns an internal Forward action to have the request rerouted without an HTTP redirect
			is_a($returnValue, 'NitroPack\NitroPack\Controller\Webhook\CacheClear') || is_a($returnValue, 'NitroPack\NitroPack\Controller\Webhook\Config') || is_a($returnValue, 'NitroPack\NitroPack\Controller\Webhook\CacheReady')) { // NitroPack webhooks
			return $returnValue;
		}

		$store = $this->storeManager->getStore();
		$storeViewId = $store->getId();
		$storeId = $store->getStoreGroupId();
		$websiteId = $store->getWebsiteId();

		$route = $this->request->getFullActionName();

		$layout = $websiteId . '_' . $storeId . '_' . $storeViewId . '_' . $route;

		if (defined('NITROPACK_DEBUG') && NITROPACK_DEBUG) {
			header('X-Nitro-Layout: ' . $layout);
		}

		if (!$this->nitro->isCachableRoute($route)) {
			header('X-Nitro-Disabled: 1', true);
			return $returnValue;
		}

		$currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

		$this->cookies->setCurrencyCookie($currencyCode);
		$this->cookies->setStoreCookie($storeViewId);

		if ($this->nitro->hasRemoteCache($layout)) {
			header('X-Nitro-Cache: HIT', true);
			$this->nitro->pageCache->readfile();
			exit;
		}

		CacheTagObserver::enableObservers();

		return $returnValue;
	}

}
