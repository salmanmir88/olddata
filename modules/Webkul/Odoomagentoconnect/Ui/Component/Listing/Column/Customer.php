<?php
/**
 * Webkul Odoomagentoconnect Customer Listing Component
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
 * Webkul Odoomagentoconnect Customer Ui Component Class
 */
class Customer extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.price';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_userFactory;
    protected $_customerFactory;

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
        \Magento\Customer\Model\Customer $customerFactory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
    
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_customerFactory=$customerFactory;
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
                    $customerObj = $this->_customerFactory->load($item['magento_id']);
                    $item['email'] = '<a href="'.$this->_urlBuilder->getUrl('customer/index/edit', ['id' => $item['magento_id']]).'">'.$customerObj->getEmail().'</a>';
                }
            }
        }
        return $dataSource;
    }
}
