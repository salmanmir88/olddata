<?php
/**
 * Webkul Odoomagentoconnect Set Listing Component
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Set extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.price';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_userFactory;
    protected $_setFactory;

    /** Url Path */
    const SET_PATH_VIEW = 'catalog/product_set/edit';

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        \Webkul\Odoomagentoconnect\Model\SetFactory $setFactory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
    
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_setFactory=$setFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['magento_id'])) {
                    $item['name'] = '<a href="'.$this->urlBuilder->getUrl(self::SET_PATH_VIEW, ['id' => $item['magento_id']]).'">'.$item['name'].'</a>';
                }
            }
        }

        return $dataSource;
    }
}
