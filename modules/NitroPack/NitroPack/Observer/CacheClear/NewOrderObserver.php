<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;

use NitroPack\NitroPack\Observer\CacheClearObserver;

class NewOrderObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'sales_order_save_after' => 'newOrder'
	);

	protected $order; // Magento\Sales\Model\Order

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('orders')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['order'])) {
			return false;
		}

		$this->order = $data['order'];

		return true;
	}

	public function newOrder() {
		$items = $this->order->getItems();
		foreach ($items as $item) {
			$tag = $this->tagger->getProductTag((int)$item->getProductId());
			$this->invalidateTag($tag, 'order', '#' . $this->order->getId());
		}
	}
}
