<?php
namespace NitroPack\NitroPack\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use NitroPack\NitroPack\Controller\Adminhtml\StoreAwareAction;
use NitroPack\NitroPack\Api\NitroServiceInterface;

class Index extends StoreAwareAction {

	protected $resultPageFactory;

	protected $nitro;

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		NitroServiceInterface $nitro
	) {
		parent::__construct($context, $nitro);
		$this->resultPageFactory = $resultPageFactory;
		$this->nitro = $nitro;
	}

	protected function nitroExecute() {
		if (!$this->nitro->isConnected()) {
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setUrl($this->getUrlWithStore('NitroPack/connect/index'));
			return $resultRedirect;
		}

		$page = $this->resultPageFactory->create();

		return $page;
	}
}
?>
