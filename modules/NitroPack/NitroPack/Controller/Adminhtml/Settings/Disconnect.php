<?php
namespace NitroPack\NitroPack\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

use NitroPack\NitroPack\Controller\Adminhtml\StoreAwareAction;
use NitroPack\NitroPack\Api\NitroServiceInterface;

class Disconnect extends StoreAwareAction {
	protected $resultJsonFactory;

	protected $nitro;

	public function __construct(
		Context $context,
		NitroServiceInterface $nitro
	) {
		parent::__construct($context, $nitro);
		$this->resultJsonFactory = $this->_objectManager->create(JsonFactory::class);
		$this->nitro = $nitro;
	}

	protected function nitroExecute() {
		try {
			$this->nitro->getApi()->disableWarmup();
			$this->nitro->getApi()->resetWarmup();
			$this->nitro->disconnect($this->getStore()->getCode());
			return $this->resultJsonFactory->create()->setData(array(
				'disconnected' => true
			));
		} catch (\Exception $e) {
			return $this->resultJsonFactory->create()->setData(array(
				'disconnected' => false
			));
		}
	}
}
?>
