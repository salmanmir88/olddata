<?php
namespace Aalogics\SearchByTelephone\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
 
use Magento\Framework\Pricing\PriceCurrencyInterface;
 
class TelephoneColumn extends Column
{
	protected $_orderRepository;
	protected $_searchCriteria;
	protected $orderCollectionFactory;
 
	public function __construct(
    	PriceCurrencyInterface $priceCurrency,
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
    	$this->priceCurrency = $priceCurrency;
    	$this->orderCollectionFactory = $orderCollectionFactory;
    	parent::__construct($context, $uiComponentFactory, $components, $data);
	}
	
	public function prepareDataSource(array $dataSource)
	{
    	if (isset($dataSource['data']['items'])) {
        	foreach ($dataSource['data']['items'] as & $item) {
            	$order  = $this->_orderRepository->get($item["entity_id"]);
        		$telephone = $order->getBillingAddress()->getData("telephone");
            	$orders = $this->orderCollectionFactory->create();
                $orders->getSelect()->joinLeft(
			        ['soa1' => 'sales_order_address'],
			        "main_table.entity_id =soa1.parent_id AND soa1.address_type = 'shipping'",
			        ['telephone'=>'soa1.telephone']
			    );
                $orders->addAttributeToFilter('telephone', $telephone);
               
                if(count($orders)>1)
                {
                  $telephone = '<span style="background-color: #90EE90;">'.$telephone.'</span>';
                }      

            	$item[$this->getData('name')] = $telephone;
        	}
    	}
    	return $dataSource;
	}
}