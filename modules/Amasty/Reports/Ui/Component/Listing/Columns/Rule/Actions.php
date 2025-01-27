<?php

namespace Amasty\Reports\Ui\Component\Listing\Columns\Rule;

use Amasty\Reports\Api\Data\RuleInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

/**
 * Class Actions
 * @package Amasty\Reports\Ui\Component\Listing\Columns\Rule
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * RuleActions constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->escaper = $escaper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $item[$name]['edit'] = [
                    'href'  => $this->urlBuilder->getUrl(
                        'amasty_reports/rule/edit',
                        ['id' => $item['entity_id']]
                    ),
                    'label' => __('Edit')
                ];
                $title = $this->escaper->escapeHtml($item[RuleInterface::TITLE]);
                $item[$name]['delete'] = [
                    'href'    => $this->urlBuilder->getUrl(
                        'amasty_reports/rule/delete',
                        ['id' => $item['entity_id']]
                    ),
                    'label'   => __('Delete'),
                    'confirm' => [
                        'title'   => __('Delete %1', $title),
                        'message' => __('Are you sure you wan\'t to delete a %1 rule?', $title)
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
