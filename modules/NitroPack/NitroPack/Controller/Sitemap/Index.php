<?php
namespace NitroPack\NitroPack\Controller\Sitemap;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

use NitroPack\NitroPack\Api\NitroServiceInterface;

class Index extends Action implements HttpGetActionInterface {

	protected $nitro;

	public function __construct(Context $context) {
		parent::__construct($context);

		$this->nitro = $this->_objectManager->get(NitroServiceInterface::class);
	}

	public function execute() {
		header("Content-Type: application/xml");
		header("Cache-Control: no-cache");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		$settings = $this->nitro->getSettings();

		if ($this->nitro->isEnabled() && $settings->cacheWarmup) {
			$pageTypes = (array) $settings->warmupTypes;
			$pagePriority = $settings->warmupPriority;
			$entryTypes = array();
			foreach ($pageTypes as $type => $enabled) {
				// warmup for this page type is disabled, or the page type is disabled from caching in general
				if (!$enabled || !isset($settings->pageTypes->{$type}) || !$settings->pageTypes->{$type}) continue;
				$entryTypes[] = array(
					'type' => $type,
					'priority' => min(1, max(0, $pagePriority->{$type}))
				);
			}

			usort($entryTypes, function ($a, $b) {
				if ($a['priority'] == $b['priority']) return 0;
				elseif (($b['priority'] - $a['priority']) < 0) return -1;
				else return 1;
			});

			foreach ($entryTypes as $entryType) {
				switch ($entryType['type']) {
					case 'home':
						$this->renderHomePageEntry();
						break;
					case 'category':
						$this->renderCategoryEntries();
						break;
					case 'contact':
						$this->renderContactEntry();
						break;
					case 'info':
						$this->renderInfoPageEntries();
						break;
					case 'product':
						$this->renderProductEntries();
						break;
				}
			}
		}

		echo '</urlset>';
		exit;
	}

	protected function renderHomePageEntry() {
		$storeManager = $this->_objectManager->get(StoreManagerInterface::class);
		$this->renderEntry($storeManager->getStore()->getBaseUrl());
	}

	protected function renderProductEntries() {
		$collectionFactory = $this->_objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
		$productVisibility = $this->_objectManager->get(\Magento\Catalog\Model\Product\Visibility::class);

		$collection = $collectionFactory->create();
		$collection->addAttributeToSelect('*');
		$collection->addWebsiteFilter();
		$collection->addStoreFilter();
		$collection->addFieldToFilter('status', 1);
		$collection->setVisibility($productVisibility->getVisibleInSiteIds());

		foreach ($collection as $product) {
			$this->renderEntry($product->getProductUrl());
		}
	}

	protected function renderCategoryEntries() {
		$storeManager = $this->_objectManager->get(StoreManagerInterface::class);
		$store = $storeManager->getStore();
		
		$collectionFactory = $this->_objectManager->get(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class);
		$collection = $collectionFactory->create();
		$collection->addAttributeToSelect('*');
		$collection->setStore($store);
		$collection->addFieldToFilter('is_active', 1);
		$collection->addAttributeToSort('level');
		$collection->addAttributeToSort('position');
		$collection->addAttributeToSort('path');

		foreach ($collection as $category) {
			$this->renderEntry($category->getUrl());
		}
	}

	protected function renderInfoPageEntries() {
		$storeManager = $this->_objectManager->get(StoreManagerInterface::class);
		$store = $storeManager->getStore();
		$pageHelper = $this->_objectManager->create(\Magento\Cms\Helper\Page::class);

		$collectionFactory = $this->_objectManager->get(\Magento\Cms\Model\ResourceModel\Page\CollectionFactory::class);
		$collection = $collectionFactory->create();
		$collection->addStoreFilter($store);
		$collection->addFieldToFilter('is_active', 1);

		foreach ($collection as $page) {
			$this->renderEntry($pageHelper->getPageUrl($page->getId()));
		}
	}

	protected function renderPopularSearchEntry() {
		$this->renderEntry($this->_url->getUrl('search/term/popular'));
	}

	protected function renderContactEntry() {
		$this->renderEntry($this->_url->getUrl('contact'));
	}

	protected function renderEntry($url) {
		echo '<url>';
		echo '<loc>';
		echo '<![CDATA[' . $url . ']]>';
		echo '</loc>';
		echo '</url>';
	}

}