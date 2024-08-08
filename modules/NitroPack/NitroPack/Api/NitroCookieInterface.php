<?php
namespace NitroPack\NitroPack\Api;

interface NitroCookieInterface {

	public function get($name);
	public function set($name, $value, $duration=86400);
	public function delete($name);

	public function getStoreCookie();
	public function setStoreCookie($storeCode);

	public function getCurrencyCookie();
	public function setCurrencyCookie($currencyCode);

}