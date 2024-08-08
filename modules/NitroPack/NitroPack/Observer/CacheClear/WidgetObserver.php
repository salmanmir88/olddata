<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;

use NitroPack\NitroPack\Observer\CacheClearObserver;

class WidgetObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'widget_widget_instance_save_commit_after'   => 'saved',
		'widget_widget_instance_delete_commit_after' => 'deleted'
	);

	protected $widget; // Magento\Widget\Model\Widget\Instance

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('widgets')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['object']) || !is_a($data['object'], WidgetInstance::class)) {
			return false;
		}

		$this->widget = $data['object'];

		return true;
	}

	public function saved(Observer $observer) {
		$widgetName = $this->widget->getTitle();
		if (!$widgetName || $widgetName == '') {
			$widgetName = '#' . $this->widget->getId();
		}
		$this->invalidateTag('page', 'widget', $widgetName);
		$this->invalidateTag('block', 'widget', $widgetName);
	}

	public function deleted(Observer $observer) {
		$widgetName = $this->widget->getTitle();
		if (!$widgetName || $widgetName == '') {
			$widgetName = '#' . $this->widget->getId();
		}
		$this->purgeTagPageCache('page', 'widget', $widgetName);
		$this->purgeTagPageCache('block', 'widget', $widgetName);
	}
	
}
