<?php
namespace NitroPack\NitroPack\Controller\Adminhtml\Warmup;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;

use NitroPack\NitroPack\Controller\Adminhtml\StoreAwareAction;
use NitroPack\NitroPack\Api\NitroServiceInterface;
use NitroPack\NitroPack\Api\CacheWarmupInterface;

class Save extends StoreAwareAction {

	protected $nitro;
	protected $cacheWarmup;

	protected $request;
	
	public function __construct(
		Context $context,
		NitroServiceInterface $nitro,
		RequestInterface $request,
		CacheWarmupInterface $cacheWarmup
	) {
		parent::__construct($context, $nitro);
		$this->nitro = $nitro;
		$this->cacheWarmup = $cacheWarmup;
		$this->request = $request;
		$this->resultJsonFactory = $this->_objectManager->create(JsonFactory::class);
	}

	public function nitroExecute() {
		$storeViews = $this->request->getPostValue('storeViews', null);
		$currencies = $this->request->getPostValue('currencies', null);
		$pageTypes = $this->request->getPostValue('pageTypes', null);

		$config = array(
			'storeViews' => $storeViews,
			'currencies' => $currencies,
			'pageTypes' => $pageTypes
		);

		$this->convertJSBooleans($config);

		$this->cacheWarmup->setConfig($config);

		return $this->resultJsonFactory->create()->setData(['saved' => true]);
	}

	protected function convertJSBooleans(&$config) {
		foreach ($config as $key => &$val) {
			if (is_array($val)) {
				$this->convertJSBooleans($val);
			} elseif ($val === 'true') {
				$val = true;
			} elseif ($val === 'false') {
				$val = false;
			}
		}
	}

}