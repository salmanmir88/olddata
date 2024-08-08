<?php

namespace Dakha\CustomWork\Ui\Component\Listing\Column\Track;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class OrderTracking extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_countryFactory;
    private $productRepository;
    protected $_date; 

 
    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, CountryFactory $countryFactory, ProductRepositoryInterface $productRepository,TimezoneInterface $date, array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_countryFactory = $countryFactory;
        $this->productRepository = $productRepository;
        $this->_date =  $date;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order  = $this->_orderRepository->get($item["entity_id"]);
                if($this->getAlbumReleaseDate($order)){
                   $item[$this->getData('name')] = $this->getAlbumReleaseDate($order);
                }else{
                   $item[$this->getData('name')] = $this->getInvoiceDate($order)??"";
                }
            }
        }
        return $dataSource;
    }
    
    public function getOrderCreateAt($order)
    {
       return $order->getCreatedAt();
    }

    public function getAlbumReleaseDate($order)
    {
       $releaseDate = ''; 
       try {
           foreach ($order->getAllVisibleItems() as $_item) {
            $product = $this->productRepository->get($_item->getSku());
             if($product->getReleaseDate()){
                $releaseDate = $product->getReleaseDate();
                if($releaseDate >= $this->getOrderInvoiceDate($order))
                {
                  $releaseDate = $product->getReleaseDate();
                  break; 
                }else{
                  $releaseDate = $this->getOrderInvoiceDate($order);   
                }   
             }
           }

           $shipmentDate = $this->getShipmentDate($order);
           $currentDate = $this->_date->date()->format('Y-m-d H:i:s');
           if($shipmentDate){
                $currentDate = $shipmentDate; 
           }
           if($currentDate > $releaseDate && $releaseDate){
             return $this->dateDiffInDays($releaseDate,$currentDate);
           }elseif ($releaseDate >= $currentDate) {
             return $this->dateDiffInDays($currentDate,$releaseDate);  
           }
        } catch (\Exception $e) {
          return;    
       }
       return;
    }
  
  public function getOrderInvoiceDate($order)
    {
        $orderInvoiceDate = '';
        foreach($order->getInvoiceCollection() as $invoice){
            $orderInvoiceDate = $invoice->getCreatedAt();
            break;
        }
        return $orderInvoiceDate;
    }
    
  public function getInvoiceDate($order)
    {
        $orderInvoiceDate = '';
        foreach($order->getInvoiceCollection() as $invoice){
            $orderInvoiceDate = $invoice->getCreatedAt();
            break;
        }

        $shipmentDate = $this->getShipmentDate($order);
        $currentDate = $this->_date->date()->format('Y-m-d H:i:s');
        if($shipmentDate){
            $currentDate = $shipmentDate; 
        }
        if($currentDate > $orderInvoiceDate && $orderInvoiceDate){
             return $this->dateDiffInDays($orderInvoiceDate,$currentDate);
        }
        return;
    }

   public function getShipmentDate($order)
    {
        $orderShipmentDate = '';
        foreach($order->getShipmentsCollection() as $shipment){
            $orderShipmentDate = $shipment->getCreatedAt();
            break;
        }
        return;
    }

    // between two dates.
   public function dateDiffInDays($date1, $date2) 
    {
      $diff = strtotime($date2) - strtotime($date1);
      return abs(round($diff / 86400));
    }

}