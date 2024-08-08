<?php
namespace NitroPack\NitroPack\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

use NitroPack\NitroPack\Api\NitroServiceInterface;

class ConnectBlock extends Template {

	protected $nitro;

	protected $store;
	protected $_backendUrl;
	protected $_storeManager;

	public function __construct(
		Context $context, // required as part of the Magento\Backend\Block\Template constructor
		NitroServiceInterface $nitro, // dependency injection'ed
		UrlInterface $backendUrl, // dependency injection'ed
		StoreManagerInterface $storeManager, // dependency injection'ed
		RequestInterface $request, // dependency injection'ed
		array $data = [] // required as part of the Magento\Backend\Block\Template constructor
	) {
		parent::__construct($context, $data);

		$this->nitro = $nitro;
		$this->_backendUrl = $backendUrl;
		$this->_storeManager = $storeManager;
		$this->_request = $request;
	}

	public function getSaveUrl() {
		return $this->_backendUrl->getUrl('NitroPack/connect/save', array(
			'store' => $this->getStore()->getId()
		));
	}

	public function getStoreUrl() {
		return $this->getStore()->getBaseUrl();
	}

	public function getStoreName() {
		return $this->getStore()->getName();
	}

	public function getStoreCode() {
		return $this->getStore()->getCode();
	}

	protected function getStore() {
		if (!$this->store) {
			$storeId = (int) $this->_request->getParam('store', 0);
			$store = $this->_storeManager->getStore($storeId);
			$this->store = $store;
		}
		return $this->store;
	}
}