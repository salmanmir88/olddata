<?php
/**
 * Copyright © CustomerOrderGrid All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomerOrderGrid\Rewrite\Magento\Customer\Block\Adminhtml\Edit\Tab;
use Magento\Customer\Controller\RegistryConstants;
class Orders extends \Magento\Customer\Block\Adminhtml\Edit\Tab\Orders
{
    /**
     * Sales reorder
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $_salesReorder = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Helper\Reorder $salesReorder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Magento\Sales\Helper\Reorder $salesReorder,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_salesReorder = $salesReorder;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $collectionFactory,$salesReorder,$coreRegistry,$data);
    }
    
   /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_orders_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

      /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->getReport('sales_order_grid_data_source')->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'increment_id'
        )->addFieldToSelect(
            'customer_id'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'grand_total'
        )->addFieldToSelect(
            'order_currency_code'
        )->addFieldToSelect(
            'payment_method'
        )->addFieldToSelect(
            'status'
        )->addFieldToSelect(
            'store_id'
        )->addFieldToSelect(
            'billing_name'
        )->addFieldToSelect(
            'shipping_name'
        )->addFieldToFilter(
            'customer_id',
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'increment_id', 
            ['header' => __('Order #'), 
            'target' => '_blank', 
            'width' => '100', 
            'renderer' => \Dakha\CustomerOrderGrid\Rewrite\Magento\Customer\Block\Adminhtml\Order\Renderer\Action::class,
            'index' => 'increment_id'
            ]
        );

        $this->addColumn(
            'created_at',
            ['header' => __('Purchased'), 'index' => 'created_at', 'type' => 'datetime']
        );

        $this->addColumn('payment_method', ['header' => __('Payment Method'), 'index' => 'payment_method']);

        $this->addColumn('billing_name', ['header' => __('Bill-to Name'), 'index' => 'billing_name']);

        $this->addColumn('shipping_name', ['header' => __('Ship-to Name'), 'index' => 'shipping_name']);
        
        $this->addColumn(
            'status', 
            ['header' => __('Order Status'),  
            'width' => '100', 
            'renderer' => \Dakha\CustomerOrderGrid\Rewrite\Magento\Customer\Block\Adminhtml\Order\Renderer\Status::class,
            'index' => 'status'
            ]
        );

        $this->addColumn(
            'grand_total',
            [
                'header' => __('Order Total'),
                'index' => 'grand_total',
                'type' => 'currency',
                'currency' => 'order_currency_code',
                'rate'  => 1
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                ['header' => __('Purchase Point'), 'index' => 'store_id', 'type' => 'store', 'store_view' => true]
            );
        }

        if ($this->_salesReorder->isAllow()) {
            $this->addColumn(
                'action',
                [
                    'header' => 'Action',
                    'filter' => false,
                    'sortable' => false,
                    'width' => '100px',
                    'renderer' => \Magento\Sales\Block\Adminhtml\Reorder\Renderer\Action::class
                ]
            );
        }
        return $this;

    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sales/order/view',
            ['target' => '_blank','order_id' => $row->getId(), 'customer_id' =>  $this->getRequest()->getParam('id')]
        );
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('customer/*/orders', ['target' => '_blank','_current' => true]);
    }

}

