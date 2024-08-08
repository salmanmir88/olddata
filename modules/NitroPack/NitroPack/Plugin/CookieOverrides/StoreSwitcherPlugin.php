<?php
namespace NitroPack\NitroPack\Plugin\CookieOverrides;

use Magento\Store\Model\StoreSwitcher;

use NitroPack\NitroPack\Api\NitroCookieInterface;

class StoreSwitcherPlugin {

	protected $cookie;

	public function __construct(NitroCookieInterface $cookie) {
		$this->cookie = $cookie;
	}

	public function beforeSwitch(StoreSwitcher $subject, $fromStore, $targetStore, $redirectUrl) {
		$this->cookie->setStoreCookie($targetStore->getId());
		return null;
	}

}