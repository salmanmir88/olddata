<?php
namespace Dakha\OrderReport\Block\Adminhtml\Salesitem;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Dakha\OrderReport\Model\salesitemFactory
     */
    protected $_salesitemFactory;

    /**
     * @var \Dakha\OrderReport\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Dakha\OrderReport\Model\salesitemFactory $salesitemFactory
     * @param \Dakha\OrderReport\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Dakha\OrderReport\Model\SalesitemFactory $SalesitemFactory,
        \Dakha\OrderReport\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_salesitemFactory = $SalesitemFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _initSelect()
    {

            $this->addFilterToMap('created_at', 'main_table.created_at');
            parent::_initSelect();
    }

    /**
     * @return void
     */
    protected function _construct()
    {

        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {


        $collection = $this->_salesitemFactory->create()->getCollection();
        $collection->getSelect()->columns(['qty_ordered' => new \Zend_Db_Expr('SUM(qty_ordered)')])->group('product_id');
        $collection->getSelect()->joinLeft(
                                        array('second' => 'catalog_product_entity_varchar'),
                                        'main_table.product_id = second.entity_id and attribute_id = 156',
                                         array('bare_code' => 'value')
                                        );
        $collection->getSelect()->join(
                                        array('third' => 'sales_order'),
                                        'main_table.order_id = third.entity_id',
                                         array('customer_email',
                                               'increment_id',
                                               'customer_firstname',
                                               'customer_lastname',
                                           )
                                        );
        $collection->getSelect()->join(
                                        array('fourth' => 'sales_order_address'),
                                        'main_table.order_id = fourth.parent_id',
                                         array('telephone',
                                               'city',
                                               'street',
                                               'country_id',
                                           )
                                        );

        $collection->addFilterToMap('created_at', 'main_table.created_at');

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'created_at',
            [
                'header' => __('Date'),
                'type' => 'date',
                'index' => 'created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
       
        $this->addColumn(
                    'increment_id',
                    [
                        'header' => __('Increment id'),
                        'index' => 'increment_id',
                    ]
                );  


       $this->addColumn(
                    'customer_email',
                    [
                        'header' => __('Customer email'),
                        'index' => 'customer_email',
                    ]
                );  
        
       $this->addColumn(
                    'customer_firstname',
                    [
                        'header' => __('Customer firstname'),
                        'index' => 'customer_firstname',
                    ]
                );   

        $this->addColumn(
                            'customer_lastname',
                            [
                                'header' => __('Customer lastname'),
                                'index' => 'customer_lastname',
                            ]
                        );   

        $this->addColumn(
                            'telephone',
                            [
                                'header' => __('Telephone'),
                                'index' => 'telephone',
                            ]
                        );                   

        $this->addColumn(
                            'city',
                            [
                                'header' => __('City'),
                                'index' => 'city',
                            ]
                        );      

        $this->addColumn(
                            'street',
                            [
                                'header' => __('Street'),
                                'index' => 'street',
                            ]
                        );               

        $this->addColumn(
                            'country_id',
                            [
                                'header' => __('Country'),
                                'index' => 'country_id',
                            ]
                        );                               

 

       $this->addColumn(
                    'sku',
                    [
                        'header' => __('SKU'),
                        'index' => 'sku',
                    ]
                );      
       
        $this->addColumn(
                    'name',
                    [
                        'header' => __('Name'),
                        'index' => 'name',
                    ]
                );
        $this->addColumn(
                    'bare_code',
                    [
                        'header' => __('Bare Code'),
                        'index' => 'bare_code',
                    ]
                );
        $this->addColumn(
                    'qty_ordered',
                    [
                        'header' => __('Qty Ordered'),
                        'index' => 'qty_ordered',
                    ]
                );
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'item_id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
        

        
           $this->addExportType($this->getUrl('orderreport/*/exportCsv', ['_current' => true]),__('CSV'));
           $this->addExportType($this->getUrl('orderreport/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('salesitem');

        return $this;
    }
        

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('orderreport/*/index', ['_current' => true]);
    }

    /**
     * @param \Dakha\OrderReport\Model\salesitem|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        
    }

    

}