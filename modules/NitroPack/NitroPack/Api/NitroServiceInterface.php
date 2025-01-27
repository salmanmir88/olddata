<?php
namespace NitroPack\NitroPack\Api;

interface NitroServiceInterface {

	public function extensionVersion();
	public function sdkVersion();

	public function reload($storeViewCode=null);
	public function disconnect($storeViewCode=null);

	public function isConnected();
	public function isEnabled();
	public function isCacheable(); // checks that can be done before the layout is known
	public function isCachableRoute($route); // checks that can only be done after the request has been routed, $route is the ->getFullActionName() of the Magento\Framework\App\Request\Http that is about to be executed, typically module_controller_action

	public function getSettings();
	public function setWarmupSettings($enabled, $pageTypes, $currencies);
	public function getBuiltInPageTypeRoutes();

	public function getSiteId();
	public function setSiteId($newSiteId);
	
	public function getSiteSecret();
	public function setSiteSecret($newSiteSecret);
	
	public function persistSettings();
	
	public function getSdk();

}