<?php
namespace NitroPack\NitroPack\Plugin\CookieOverrides;

use Magento\Framework\App\Http\Context;
use Magento\Framework\ObjectManagerInterface;

class CurrencyHttpContextPlugin extends CookieOverride {
	// For requests coming from NitroPack only
	// Enforces the return value for the Context::CONTEXT_CURRENCY key of Magento\Framework\App\Http\Context::getValue and ::getData based on our own currency cookie

	public function __construct() {
		parent::__construct();
	}

	public function aroundGetValue(Context $subject, callable $proceed, $name) {
		if ($name == Context::CONTEXT_CURRENCY && $this->shouldOverride()) {
			return $this->getCurrencyCookie();
		}
		return $proceed($name);
	}

	public function afterData(Context $subject, $result) {
		if ($this->shouldOverride() && is_array($result) && isset($result[Context::CONTEXT_CURRENCY])) {
			$result[Context::CONTEXT_CURRENCY] = $this->getCurrencyCookie();
		}
		return $result;
	}
}