<?php
namespace NitroPack\NitroPack\Plugin\CacheDelivery;

use Magento\Framework\App\RequestInterface;

use NitroPack\NitroPack\Api\NitroService;
use NitroPack\NitroPack\Api\NitroServiceInterface;
use NitroPack\NitroPack\Observer\CacheTagObserver;

class LocalCachePlugin {
	// Checks if there is local cache for the current request as soon as possible. Executed before Magento\Framework\App\FrontController::dispatch

	protected $nitro = null;

	public function __construct(NitroServiceInterface $nitro) {
		$this->nitro = $nitro;
	}

	// The RequestInterface below is not injected, it's passed to the dispatch function. We can inject it in the controller, but it is not yet routed, so we should use the one passed in
	public function beforeDispatch(\Magento\Framework\App\FrontController $subject, RequestInterface $request) {
		if (headers_sent() || NitroService::isANitroRequest()) {
			return null;
		}

		if (!$this->nitro->isConnected() || !$this->nitro->isEnabled()) {
			header('X-Nitro-Disabled: 1');
			return null;
		}

		header('X-Nitro-Cache: MISS');
		header('X-Nitro-Intergration-Version:' . $this->nitro->extensionVersion());
		header('X-Nitro-Sdk-Version:' . $this->nitro->sdkVersion());

		CacheTagObserver::disableObservers();

		if (!$this->nitro->isCacheable()) { // Magento specific checks if the request can be cached
			header('X-Nitro-Distabled: 1', true);
			return null;
		}

		if ($this->nitro->hasLocalCache()) {
			header('X-Nitro-Cache: HIT', true);
			$this->nitro->pageCache->readfile();
			exit;
		}

		CacheTagObserver::enableObservers();
		return null;
	}

}