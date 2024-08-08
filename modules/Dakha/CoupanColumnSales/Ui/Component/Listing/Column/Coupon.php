<?php
namespace Dakha\CoupanColumnSales\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Directory\Model\CountryFactory;
 
class Coupon extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_countryFactory;
 
    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, CountryFactory $countryFactory, array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('sales_order');

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $sql = "Select * FROM " . $tableName." Where entity_id= ".$item["entity_id"]."";
                $result = $connection->fetchRow($sql); 
                $couponCode = (isset($result['coupon_code']))?$result['coupon_code']:"";
                $item[$this->getData('name')] = $couponCode;
            }
        }
        return $dataSource;
    }
}