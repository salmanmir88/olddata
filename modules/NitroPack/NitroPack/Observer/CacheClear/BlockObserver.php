<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;
use Magento\Cms\Model\Block;

use NitroPack\NitroPack\Observer\CacheClearObserver;

class BlockObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'cms_block_save_commit_after'   => 'saved',
		'cms_block_delete_commit_after' => 'deleted'
	);

	protected $block; // Magento\Cms\Model\Block

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('blocks')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['object']) || !is_a($data['object'], Block::class)) {
			return false;
		}

		$this->block = $data['object'];

		return true;
	}

	public function saved(Observer $observer) {
		$tag = $this->tagger->getBlockTag($this->block);
		$blockName = $this->block->getTitle();
		if (!$blockName || $blockName == '') {
			$blockName = '#' . $this->block->getId();
		}
		
		$this->invalidateTag($tag, 'block', $blockName);
	}

	public function deleted(Observer $observer) {
		$tag = $this->tagger->getBlockTag($this->block);
		$blockName = $this->block->getTitle();
		if (!$blockName || $blockName == '') {
			$blockName = '#' . $this->block->getId();
		}
		
		$this->purgeTagComplete($tag, 'block', $blockName);
	}
	
}
