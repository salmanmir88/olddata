<?php 
namespace NitroPack\NitroPack\Api;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\Config\ConfigInterface;

class NitroCookie implements NitroCookieInterface {

	const STORE_COOKIE    = 'X-Nitro-Store';
	const CURRENCY_COOKIE = 'X-Nitro-Currency';
	const COOKIE_DURATION = 30*24*60*60;

	private $cookieManager;
	private $cookieMetadataFactory;
	private $sessionConfig;

	public function __construct(
		CookieManagerInterface $cookieManager,
		CookieMetadataFactory $cookieMetadataFactory,
		ConfigInterface $sessionConfig
	) {
		$this->cookieManager = $cookieManager;
		$this->cookieMetadataFactory = $cookieMetadataFactory;
		$this->sessionConfig = $sessionConfig;
	}

	public function get($name) {
		return $this->cookieManager->getCookie($name);
	}

	public function set($name, $value, $duration = 86400) {
		$metadata = $this->cookieMetadataFactory
			->createPublicCookieMetadata()
			->setDuration($duration)
			->setPath($this->sessionConfig->getCookiePath())
			->setDomain($this->sessionConfig->getCookieDomain());

		$this->cookieManager->setPublicCookie($name, $value, $metadata);
	}

	public function delete($name) {
		$this->cookieManager->deleteCookie($name, null);
	}

	public function getStoreCookie() {
		return $this->get(static::STORE_COOKIE);
	}

	public function setStoreCookie($storeCode) {
		return $this->set(static::STORE_COOKIE, $storeCode, static::COOKIE_DURATION);
	}

	public function getCurrencyCookie() {
		return $this->get(static::CURRENCY_COOKIE);
	}

	public function setCurrencyCookie($currencyCode) {
		return $this->set(static::CURRENCY_COOKIE, $currencyCode, static::COOKIE_DURATION);
	}

}