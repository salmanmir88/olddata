<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;
use Magento\Cms\Model\Page;

use NitroPack\NitroPack\Observer\CacheClearObserver;

class PageObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'cms_page_save_commit_after'   => 'saved',
		'cms_page_delete_commit_after' => 'deleted'
	);

	protected $page; // Magento\Cms\Model\Page

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('pages')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['object']) || !is_a($data['object'], Page::class)) {
			return false;
		}

		$this->page = $data['object'];

		return true;
	}

	public function saved(Observer $observer) {
		$tag = $this->tagger->getPageTag($this->page);
		$pageName = $this->page->getTitle();
		if (!$pageName || $pageName == '') {
			$pageName = '#' . $this->page->getId();
		}
		
		$this->invalidateTag($tag, 'page', $pageName);
	}

	public function deleted(Observer $observer) {
		$tag = $this->tagger->getPageTag($this->page);
		$pageName = $this->page->getTitle();
		if (!$pageName || $pageName == '') {
			$pageName = '#' . $this->page->getId();
		}
		
		$this->purgeTagComplete($tag, 'page', $pageName);
	}
	
}
