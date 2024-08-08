<?php
/**
 * Webkul Odoomagentoconnect Attribute Listing Component
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
use Magento\Framework\UrlInterface;

/**
 * Webkul Odoomagentoconnect Attribute Ui Component Class
 */
class Attribute extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.price';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_userFactory;
    protected $_attributeFactory;

    /** Url Path */
    const ATTRIBUTE_PATH_VIEW = 'catalog/product_attribute/edit';

    /**
     * @param ContextInterface                           $context
     * @param UiComponentFactory                         $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array                                      $components
     * @param array                                      $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        \Webkul\Odoomagentoconnect\Model\AttributeFactory $attributeFactory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
    
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_attributeFactory=$attributeFactory;
        $this->urlBuilder = $urlBuilder;
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
                    $item['name'] = '<a href="'.$this->urlBuilder->getUrl(self::ATTRIBUTE_PATH_VIEW, ['attribute_id' => $item['magento_id']]).'">'.$item['name'].'</a>';
                }
            }
        }
        return $dataSource;
    }
}
