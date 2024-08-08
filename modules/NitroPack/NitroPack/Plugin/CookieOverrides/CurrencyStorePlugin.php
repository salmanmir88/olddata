<?php
namespace NitroPack\NitroPack\Plugin\CookieOverrides;

use Magento\Store\Model\Store;
use Magento\Framework\ObjectManagerInterface;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;

use NitroPack\NitroPack\Api\NitroServiceInterface;
use NitroPack\NitroPack\Api\NitroCookieInterface;

class CurrencyStorePlugin extends CookieOverride {
	// For requests coming from NitroPack only
	// Enforces which currency/code/rate gets returned by the Magento\Store\Model\Store::getCurrentCurrency, ::getCurrentCurrencyCode and ::getCurrentCurrencyRate base on our own currency cookie

	protected $currencyFactory;
	protected $cookie;
	protected $nitroCurrency = null;

	public function __construct(CurrencyFactory $currencyFactory, NitroCookieInterface $cookie) {
		parent::__construct();
		$this->currencyFactory = $currencyFactory;
		$this->cookie = $cookie;
	}

	public function beforeSetCurrentCurrencyCode(Store $subject, $code) {
		$this->cookie->setCurrencyCookie($code);
		return null;
	}

	public function afterGetCurrentCurrency(Store $subject, $result) {
		if ($this->shouldOverride() &&
			(!is_a($result, Currency::class) || $result->getCode() != $this->getCurrencyCookie())) {
			$result = $this->getNitroCurrency($subject);
		}
		if ($result) {
			return $result;
		} else {
			return $subject->getDefaultCurrency();
		}
	}

	public function afterGetCurrentCurrencyCode(Store $subject, $result) {
		if (!$this->shouldOverride()) {
			return $result;
		}
		if ($this->getCurrencyCookie()) {
			return $this->getCurrencyCookie();
		} else {
			return $subject->getDefaultCurrencyCode();
		}
	}

	public function afterGetCurrentCurrencyRate(Store $subject, $result) {
		if (!$this->shouldOverride()) {
			return $result;
		}
		return $subject->getBaseCurrency()->getRate($subject->getCurrentCurrency());
	}

	protected function getNitroCurrency(Store $subject) {
		if ($this->nitroCurrency == null) {
			$cookie = $this->getCurrencyCookie() ? $this->getCurrencyCookie() : $subject->getDefaultCurrencyCode();
			$this->nitroCurrency = $this->currencyFactory->create()->load($cookie);
		}
		return $this->nitroCurrency;
	}

}