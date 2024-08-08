<?php
namespace NitroPack\NitroPack\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

use NitroPack\NitroPack\Api\NitroServiceInterface;

abstract class StoreAwareAction extends Action {

	protected $nitro = null;
	protected $store = null;
	protected $storeManager = null;
	protected $usedDefaultStore = false;

	public function __construct(
		Context $context,
		NitroServiceInterface $nitro
	) {
		parent::__construct($context);
		$this->nitro = $nitro;
		$this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
	}

	public function execute() {
		$storeId = (int) $this->getRequest()->getParam('store');

		if ($storeId == 0) {
			// This happens when the user has selected "All store views", use the default configured store
			// @TODO the user should be notified that they're editing the settings for the default store view, not all store views
			$storeId = $this->storeManager->getDefaultStoreView()->getId();
			$this->usedDefaultStore = true;
		}

		$this->store = $this->storeManager->getStore($storeId);
		$this->nitro->reload($this->store->getCode());
		return $this->nitroExecute();
	}

	public function getUrlWithStore($routePath = null, $routeParams = null) { // returns an admin URL
		if ($routeParams == null) {
			$routeParams = array();
		}
		$routeParams['store'] = $this->store->getId();
		return $this->_backendUrl->getUrl($routePath, $routeParams);
	}

	protected function getStore() {
		return $this->store;
	}

	protected abstract function nitroExecute();

}