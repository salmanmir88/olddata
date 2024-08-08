<?php
/**
 * Webkul Odoomagentoconnect Product Listing Component
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Webkul Odoomagentoconnect Product Ui Component Class
 */
class ProductActions extends Column
{
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $criteria,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Model\Product $productManager,
        array $components = [],
        array $data = []
    ) {
        $this->_searchCriteria  = $criteria;
        $this->_productManager = $productManager;
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $prodIds = $this->_productManager->getCollection()
                ->addAttributeToFilter('type_id', ['neq' => 'configurable'])
                ->addAttributeToSelect('entity_id')
                ->getAllIds();
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['magento_id']) && in_array($item['magento_id'], $prodIds)) {
                    $productObj = $this->_productManager->load($item['magento_id']);
                    if ($productObj->getId() == $item['magento_id']) {
                        $item['entity_id'] = [
                            'href' => $this->_urlBuilder->getUrl(
                                'catalog/product/edit',
                                ['id' => $item['magento_id']]
                            ),
                            'label' => __('Edit'),
                            'hidden' => false,
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
