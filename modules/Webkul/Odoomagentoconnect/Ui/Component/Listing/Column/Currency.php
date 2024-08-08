<?php
/**
 * Webkul Odoomagentoconnect Currency Listing Component
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
 * Webkul Odoomagentoconnect Currency Ui Component Class
 */
class Currency extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.price';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_userFactory;
    protected $_currencyFactory;

    /**
     * @param ContextInterface                            $context
     * @param UiComponentFactory                          $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param array                                       $components
     * @param array                                       $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Odoomagentoconnect\Model\CurrencyFactory $currencyFactory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
    
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_currencyFactory=$currencyFactory;
        $this->_userFactory=$userFactory;
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $currency=$this->_currencyFactory->create()->load($item['currency_id']);
                $user=$this->_userFactory->create()->load($currency->getUserId());
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $fieldName;
                }
            }
        }

        return $dataSource;
    }
}
