<?php
namespace NitroPack\NitroPack\Controller\Adminhtml\Connect;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

use NitroPack\NitroPack\Controller\Adminhtml\StoreAwareAction;
use NitroPack\NitroPack\Api\NitroServiceInterface;
use NitroPack\NitroPack\Helper\AdminFrontendUrl;

use NitroPack\Url as NitropackUrl;

class Save extends StoreAwareAction {

	protected $nitro;
	protected $urlHelper;
	protected $resultJsonFactory;
	protected $request;

	protected $siteId = null;
	protected $siteSecret = null;
	protected $errors = array();

	public function __construct(
		Context $context,
		NitroServiceInterface $nitro,
		AdminFrontendUrl $urlHelper
	) {
		parent::__construct($context, $nitro);
		$this->nitro = $nitro;
		$this->resultJsonFactory = $this->_objectManager->create(JsonFactory::class);
		$this->urlHelper = $urlHelper;
		$this->request = $this->getRequest();
	}

	public function nitroExecute() {
		if ($this->validateSiteCredentials()) {
			$this->saveSettings();
			$this->nitro->reload($this->getStore()->getCode());

			$urls = $this->getWebhookUrls();

			foreach ($urls as $type => $url) {
				$this->nitro->getApi()->setWebhook($type, $url);
			}

			return $this->resultJsonFactory->create()->setData(array(
				'connected' => true,
				'redirect' => $this->getUrlWithStore('NitroPack/settings/index', array(
					'store' => $this->store->getId()
				))
			));
		} else {
			return $this->resultJsonFactory->create()->setData(array(
				'connected' => false,
				'errors' => $this->errors
			));
		}
	}

	protected function validateSiteCredentials() {
		$this->siteId = $this->request->getPostValue('nitro_site_id', null);
		$this->siteSecret = $this->request->getPostValue('nitro_site_secret', null);

		if (!$this->siteId) {
			$this->errors['nitro_site_id'] = 'Site ID cannot be blank';
		}

		if (!$this->siteSecret) {
			$this->errors['nitro_site_secret'] = 'Site secret cannot be blank';
		}

		return empty($this->errors);
	}

	protected function saveSettings() {
		$this->nitro->setSiteId($this->siteId);
		$this->nitro->setSiteSecret($this->siteSecret);
		$this->nitro->persistSettings($this->store->getCode());
	}

	protected function getWebhookUrls() {
		$urls = array(
			'config'      => new NitropackUrl($this->urlHelper->getUrl('NitroPack/Webhook/Config')),
			'cache_clear' => new NitropackUrl($this->urlHelper->getUrl('NitroPack/Webhook/CacheClear')),
			'cache_ready' => new NitropackUrl($this->urlHelper->getUrl('NitroPack/Webhook/CacheReady'))
		);

		return $urls;
	}

}
?>