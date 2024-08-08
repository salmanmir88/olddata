<?php
namespace SR\ModifiedSalesOrderGrid\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
 
class Multitime extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $orderCollectionFactory;
 
    public function __construct(
        ContextInterface $context, 
        UiComponentFactory $uiComponentFactory, 
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria, 
        array $components = [], 
        array $data = [],
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     )
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $orders = $this->orderCollectionFactory->create()
                               ->addAttributeToSelect('customer_email')
                               ->addAttributeToFilter('customer_email', $item['customer_email']);
                $email = $item['customer_email'];
                if(count($orders)>1)
                {
                  $email = '<span style="background-color: #90EE90;">'.$item['customer_email'].'</span>';
                }               
                $item[$this->getData('name')] =$email;
            }
        }
        return $dataSource;
    }
}