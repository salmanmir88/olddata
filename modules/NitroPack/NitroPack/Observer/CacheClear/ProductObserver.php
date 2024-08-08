<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;

use NitroPack\NitroPack\Observer\CacheClearObserver;

class ProductObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'catalog_product_save_commit_after'   => 'saved',
		'catalog_product_delete_commit_after' => 'deleted'
	);

	protected $product; // Magento\Catalog\Model\Product

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('products')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['product'])) {
			return false;
		}

		$this->product = $data['product'];

		return true;
	}

	public function saved(Observer $observer) {
		$tag = $this->tagger->getProductTag($this->product);
		$productName = $this->product->getName();
		if (!$productName || $productName == '') {
			$productName = '#' . $this->product->getId();
		}
		
		$this->invalidateTag($tag, 'product', $productName);
	}

	public function deleted(Observer $observer) {
		$tag = $this->tagger->getProductTag($this->product);
		$productName = $this->product->getName();
		if (!$productName || $productName == '') {
			$productName = '#' . $this->product->getId();
		}
		
		$this->purgeTagComplete($tag, 'product', $productName);
	}
	
}
