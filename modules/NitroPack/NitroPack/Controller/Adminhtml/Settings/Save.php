<?php
namespace NitroPack\NitroPack\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;

use NitroPack\NitroPack\Controller\Adminhtml\StoreAwareAction;
use NitroPack\NitroPack\Api\NitroServiceInterface;
use NitroPack\NitroPack\Api\NitroCookie;
use NitroPack\NitroPack\Helper\AdminFrontendUrl;

class Save extends StoreAwareAction {

	const NITRO_CLEAR_ARRAY = '___NITRO_CLEAR_ARRAY';

	protected $request;

	protected $nitro;
	protected $urlHelper;
	protected $resultJsonFactory;

	public function __construct(
		Context $context,
		RequestInterface $request,
		NitroServiceInterface $nitro,
		AdminFrontendUrl $urlHelper
	) {
		parent::__construct($context, $nitro);
		$this->request = $request;
		$this->nitro = $nitro;
		$this->urlHelper = $urlHelper;
		$this->resultJsonFactory = $this->_objectManager->create(JsonFactory::class);
	}

	protected function nitroExecute() {
		$shouldSave = false;
		$errors = array();

		$booleans = array(
			'enabled',
			'compression',
			'cacheWarmup',

			'autoClear-products',
			'autoClear-attributes',
			'autoClear-attributeSets',
			'autoClear-reviews',
			'autoClear-categories',
			'autoClear-pages',
			'autoClear-blocks',
			'autoClear-widgets',
			'autoClear-orders',

			'pageTypes-home',
			'pageTypes-product',
			'pageTypes-category',
			'pageTypes-info',
			'pageTypes-contact',

			'warmupTypes-home',
			'warmupTypes-product',
			'warmupTypes-category',
			'warmupTypes-info',
			'warmupTypes-contact'
		);

		$arrays = array(
			'pageTypes-custom'
		);

		$oldSettings = (array) $this->nitro->getSettings();

		foreach ($booleans as $option) {
			if (($value = $this->request->getPostValue($option, null)) !== null) {
				$this->setBoolean($option, $value);
				$shouldSave = true;
			}
		}

		foreach ($arrays as $option) {
			$value = $this->getRequest()->getPostValue($option, null);
			if ($value === null) continue;

			if (is_array($value)) {
				$this->setArray($option, $value);
				$shouldSave = true;
			} elseif ($value == Save::NITRO_CLEAR_ARRAY) {
				$this->setArray($option, array());
				$shouldSave = true;
			} else {
				return $this->resultJsonFactory->create()->setData(array(
					'nope' => $value
				));
			}
		}

		if (empty($errors) && $shouldSave) {
			$this->nitro->persistSettings();

			$newSettings = (array) $this->nitro->getSettings();

			if (!$oldSettings['cacheWarmup'] && $newSettings['cacheWarmup']) {
				$sitemapUrl = $this->getWarmupSitemapUrl();
				$this->nitro->getApi()->setWarmupSitemap($sitemapUrl);
				$this->nitro->getApi()->enableWarmup();
				$this->nitro->getApi()->resetWarmup();
			} elseif ($oldSettings['cacheWarmup'] && !$newSettings['cacheWarmup']) {
				$this->nitro->getApi()->unsetWarmupSitemap();
				$this->nitro->getApi()->disableWarmup();
				$this->nitro->getApi()->resetWarmup();
			}
		}

		return $this->resultJsonFactory->create()->setData(array(
			'saved' => true
		));
	}

	protected function setBoolean($option, $value) {
		$value = (intval($value) != 0);
		if (strpos($option, '-') === false) {
			$this->nitro->getSettings()->{$option} = $value;
			return;
		}

		$ref = $this->nitro->getSettings();
		$split = explode('-', $option);
		$last = count($split)-1;

		foreach ($split as $i => $sub) {
			if ($i != $last) {
				$ref = $ref->{$sub};
			} else {
				$ref->{$sub} = $value;
			}
		}
	}

	protected function setArray($option, $value) {
		if (strpos($option, '-') === false) {
			$this->nitro->getSettings()->{$option} = $value;
			return;
		}

		$ref = $this->nitro->getSettings();
		$split = explode('-', $option);
		$last = count($split)-1;

		foreach ($split as $i => $sub) {
			if ($i != $last) {
				$ref = $ref->{$sub};
			} else {
				$ref->{$sub} = $value;
			}
		}
	}

	protected function getWarmupSitemapUrl() {
		return $this->urlHelper->getUrl('NitroPack/Sitemap/Index');
	}

}
?>
