<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;

use NitroPack\NitroPack\Observer\CacheClearObserver;

class CategoryObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'catalog_category_save_commit_after'   => 'saved',
		'catalog_category_delete_commit_after' => 'deleted',
		'catalog_category_change_products'     => 'productsChanged',
		'category_move'                        => 'moved'
	);

	protected $category; // Magento\Catalog\Model\Category

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('categories')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['category'])) {
			return false;
		}

		$this->category = $data['category'];

		return true;
	}

	public function saved(Observer $observer) {
		$tag = $this->tagger->getCategoryTag($this->category);
		$categoryName = $this->product->getName();
		if (!$categoryName || $categoryName == '') {
			$categoryName = $this->category->getId();
		}
		
		$this->invalidateTag($tag, 'category', $categoryName);
	}

	public function deleted(Observer $observer) {
		$tag = $this->tagger->getCategoryTag($this->category);
		$categoryName = $this->product->getName();
		if (!$categoryName || $categoryName == '') {
			$categoryName = '#' . $this->category->getId();
		}
		
		$this->purgeTagComplete($tag, 'category', $categoryName);
	}

	public function productsChanged(Observer $observer) {
		$tag = $this->tagger->getCategoryTag($this->category);
		$categoryName = $this->product->getName();
		if (!$categoryName || $categoryName == '') {
			$categoryName = '#' . $this->category->getId();
		}
		
		$this->invalidateTag($tag, 'category', $categoryName);
	}

	public function moved(Observer $observer) {
		$tag = $this->tagger->getCategoryTag($this->category);
		$categoryName = $this->product->getName();
		if (!$categoryName || $categoryName == '') {
			$categoryName = '#' . $this->category->getId();
		}
		
		$this->invalidateTag($tag, 'category', $categoryName);
	}
	
}
