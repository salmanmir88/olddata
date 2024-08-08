<?php
namespace NitroPack\NitroPack\Plugin\CookieOverrides;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\ObjectManagerInterface;

class CurrencySessionPlugin extends CookieOverride {
	// For requests coming from NitroPack only
	// Enforces the return value of \Magento\Framework\Session\SessionManagerInterface::getCurrentCurrency and ::getData based on our own currency cookie

	public function __construct() {
		parent::__construct();
	}

	public function around__call(SessionManagerInterface $subject, callable $proceed, $method, $arguments) {
		// The getCurrentCurrency method is handled by the magic __call method, which hands it to Magento\Framework\DataObject::__call that converts it to a "current_currency" key lookup in the session storage
		if ($method !== 'getCurrentCurrency' || !$this->shouldOverride()) return $proceed($method, $arguments);

		return $this->getCurrencyCookie();
	}

	public function aroundGetData(SessionManagerInterface $subject, callable $proceed, $key = '', $clear = false) {
		if (!$this->shouldOverride()) {
			return $proceed($key, $clear);
		}

		if ($key == 'current_currency') {
			return $this->getCurrencyCookie();
		}

		$result = $proceed($key, $clear);

		if (is_array($result) && isset($result['session_id']) && isset($result['current_currency'])) {
			$result['current_currency'] = $this->getCurrencyCookie();
		}

		return $result;
	}

	public function aroundGetCurrentCurrency(SessionManagerInterface $subject, callable $proceed) {
		// in case there is a SessionManagerInterface implementation that does not use a magic method
		if (!$this->shouldOverride()) return $proceed();

		return $this->getCurrencyCookie();
	}

}