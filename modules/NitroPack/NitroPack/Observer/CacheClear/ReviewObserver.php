<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;
use Magento\Review\Model\Review;
use Magento\Catalog\Model\Product;

use NitroPack\NitroPack\Observer\CacheClearObserver;

class ReviewObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'review_save_commit_after'   => 'purgeProductPageCache',
		'review_delete_commit_after' => 'purgeProductPageCache'
	);

	protected $review; // Magento\Review\Model\Review

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('reviews')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['object']) && is_a($data['object'], Review::class)) {
			return false;
		}

		$this->review = $data['object'];

		return true;
	}

	public function purgeProductPageCache(Observer $observer) {
		$productId = $this->review->getEntityPkValue();
		$product = $this->objectManager->create(Product::class)->load($productId);
		
		$tag = $this->tagger->getProductTag($product);

		$productName = $product->getName();
		if (!$productName || $productName == '') {
			$productName = '#' . $product->getId();
		}

		$productName .= ' (review)';

		$this->purgeTagPageCache($tag, 'product', $productName);
	}
	
}
