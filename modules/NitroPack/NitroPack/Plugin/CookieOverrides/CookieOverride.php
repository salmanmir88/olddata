<?php
namespace NitroPack\NitroPack\Plugin\CookieOverrides;

use Magento\Framework\ObjectManagerInterface;

use NitroPack\NitroPack\Api\NitroService;
use NitroPack\NitroPack\Api\NitroCookie;

class CookieOverride {

	protected $objectManager;
	protected $nitro;
	protected $cookies;
	protected static $overridesStatus = true;

	protected function __construct() { // protected - we do not want to create instances of this class, only of its children, and there's no need for it to be abstract
		
	}

	public static function toggleOverrides($setTo = true) {
		$previous = static::$overridesStatus;
		static::$overridesStatus = $setTo;
		return $previous;
	}

	protected function shouldOverride() {
		return (static::$overridesStatus && NitroService::isANitroRequest());
	}

	protected function getStoreCookie() {
		return isset($_COOKIE[NitroCookie::STORE_COOKIE]) ? $_COOKIE[NitroCookie::STORE_COOKIE] : null;
	}

	protected function getCurrencyCookie() {
		return isset($_COOKIE[NitroCookie::CURRENCY_COOKIE]) ? $_COOKIE[NitroCookie::CURRENCY_COOKIE] : null;
	}

}