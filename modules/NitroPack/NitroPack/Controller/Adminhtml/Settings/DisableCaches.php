<?php
namespace NitroPack\NitroPack\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\StateInterface;

use NitroPack\NitroPack\Controller\Adminhtml\StoreAwareAction;
use NitroPack\NitroPack\Api\NitroServiceInterface;

class DisableCaches extends StoreAwareAction {
	protected $resultJsonFactory;

	protected $nitro;

	protected static $cachesToDisable = ['full_page', 'layout', 'block_html'];

	public function __construct(
		Context $context,
		NitroServiceInterface $nitro,
		TypeListInterface $cacheTypeList,
		StateInterface $cacheState
	) {
		parent::__construct($context, $nitro);
		$this->resultJsonFactory = $this->_objectManager->create(JsonFactory::class);
		$this->nitro = $nitro;
		$this->cacheTypeList = $cacheTypeList;
		$this->cacheState = $cacheState;
	}

	protected function nitroExecute() {
		try {
			foreach (static::$cachesToDisable as $code) {
				$this->cacheTypeList->cleanType($code);
				if ($this->cacheState->isEnabled($code)) {
					$this->cacheState->setEnabled($code, false);
				}
			}

			$this->cacheState->persist();

			return $this->resultJsonFactory->create()->setData(array(
				'disabled' => true
			));
		} catch (\Exception $e) {
			return $this->resultJsonFactory->create()->setData(array(
				'disabled' => false
			));
		}
	}
}
?>