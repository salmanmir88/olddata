<?php
namespace NitroPack\NitroPack\Helper;

use Magento\Store\Model\StoreResolver;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoresData;
use Magento\Store\App\Request\StorePathInfoValidator;

use NitroPack\NitroPack\Plugin\CookieOverrides\CookieOverride;

class PristineStoreResolver extends StoreResolver {

	public function __construct(
		StoreRepositoryInterface $storeRepository,
		StoreCookieManagerInterface $storeCookieManager,
		Http $request,
		StoresData $storesData,
		StorePathInfoValidator $storePathInfoValidator
		// $runMode = ScopeInterface::SCOPE_STORE,
		// $scopeCode = null
	) {
		parent::__construct($storeRepository, $storeCookieManager, $request, $storesData, $storePathInfoValidator, "store", "default");
	}

	public function getCurrentStoreId() {
		$state = CookieOverride::toggleOverrides(false);
		$id = StoreResolver::getCurrentStoreId();
		CookieOverride::toggleOverrides($state);
		return $id;
	}

}