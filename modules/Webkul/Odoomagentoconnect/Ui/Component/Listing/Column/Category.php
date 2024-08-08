<?php
/**
 * Webkul Odoomagentoconnect Category Listing Component
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Webkul Odoomagentoconnect Category Ui Component Class
 */
class Category extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.price';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_userFactory;

    /**
     * @param ContextInterface                           $context
     * @param UiComponentFactory                         $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array                                      $components
     * @param array                                      $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Model\Category $categoryModel,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
    
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_categoryModel = $categoryModel;
        $this->_urlBuilder  = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['magento_id'])) {
                    $categoryObj = $this->_categoryModel->load($item['magento_id']);
                    $item['name'] = '<a href="'.$this->_urlBuilder->getUrl('catalog/category/edit', ['id' => $item['magento_id']]).'">'.$categoryObj->getName().'</a>';
                }
            }
        }
        return $dataSource;
    }
}
