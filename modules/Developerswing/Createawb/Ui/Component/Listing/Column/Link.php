<?php

namespace Developerswing\Createawb\Ui\Component\Listing\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Link extends Column
{
	protected $resourceConnection;
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		array $components = [],
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		array $data = []
	) {
		$this->resourceConnection = $resourceConnection;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$items) {
				if ($items['order_increment_id']) {
					$sql = "SELECT awb_link FROM `sales_order_grid`  WHERE `increment_id` = ".$items['order_increment_id']." ";
                    $row = $this->resourceConnection->getConnection()->fetchRow($sql);

					$html = '<a class="action-menu-item" data-bind="attr: {target: $col.getTarget($action()), href: $action().href}, text: $action().label, click: $col.getActionHandler($action())" data-repeat-index="0" target="_blank" href="'. $row['awb_link'] .'">';
                    $html .= __('awb link');
                    $html .= '</a>';
                    if(!empty($row['awb_link'])){	
                    $items['awb_link'] = $html;
                    }
				}
			}
		}
		return $dataSource;
	}
}