<?php
namespace NitroPack\NitroPack\Plugin\CookieOverrides;

use Magento\Store\Model\StoreResolver;

class StoreResolverPlugin extends CookieOverride {
	// For requests coming from NitroPack only - enforce which store gets resolved based on our own store cookie. Called after Magento\Store\Model\StoreResolver::getCurrentStoreId

	public function __construct() {
		parent::__construct();
	}

	public function afterGetCurrentStoreId(StoreResolver $subject, $returnValue) {
		if (!$this->shouldOverride()) return $returnValue;

		if ($this->getStoreCookie()) {
			return $this->getStoreCookie();
		} else {
			return $returnValue;
		}
	}

}