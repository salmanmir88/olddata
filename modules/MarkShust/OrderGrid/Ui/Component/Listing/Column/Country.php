<?php
namespace MarkShust\OrderGrid\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Directory\Model\CountryFactory;
 
class Country extends Column
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
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order  = $this->_orderRepository->get($item["entity_id"]);
                $getShippingCountryId = $order->getShippingAddress()->getCountryId();
                $country = $this->_countryFactory->create()->loadByCode($getShippingCountryId);
                $item[$this->getData('name')] = $country->getName();
            }
        }
        return $dataSource;
    }
}