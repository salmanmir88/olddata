<?php
namespace Evince\AWBnumber\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Awbcolumn extends Column
{

    protected $urlFactory;
    protected $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlFactory $urlFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlFactory = $urlFactory;
        $this->escaper = $escaper;
    }
    
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                    $filename = $item['awb_link'];
                    $item[$this->getData('name')] = $this->getLinkHtml($filename, $this->escaper->escapeHtml($filename));
            }
        }
        return $dataSource;
    }
    
    private function getLinkHtml($link, $filename)
    {
        return sprintf(
            '<a class="amasty-copy-on-clipboard-text" target="_blank" href="%s">%s</a>',
            $this->escaper->escapeUrl($link),
            $this->escaper->escapeHtml($filename)
        );
    }
    
}
