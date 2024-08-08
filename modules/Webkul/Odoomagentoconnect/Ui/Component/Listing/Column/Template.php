<?php
/**
 * Webkul Odoomagentoconnect Template Listing Component
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
 * Webkul Odoomagentoconnect Template Ui Component Class
 */
class Template extends Column
{
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $criteria,
        \Magento\Catalog\Model\Product $productManager,
        array $components = [],
        array $data = []
    ) {
        $this->_searchCriteria  = $criteria;
        $this->_productManager = $productManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $prodIds = $this->_productManager->getCollection()
                ->addAttributeToFilter('type_id', ['eq' => 'configurable'])
                ->addAttributeToSelect('entity_id')
                ->getAllIds();
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['magento_id']) && in_array($item['magento_id'], $prodIds)) {
                    $productObj = $this->_productManager->load($item['magento_id']);
                    if ($productObj->getId() == $item['magento_id']) {
                        $sku = $productObj->getSku();
                        $item['sku'] = $sku;
                    }
                }
            }
        }

        return $dataSource;
    }
}
