<?php

namespace Dakha\ProductNumber\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
class Productsaveafter implements ObserverInterface
{    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
    */
    protected $productCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Action
    */
    protected $productAction;
    
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Class constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param \Magento\Catalog\Model\Product\Action $productAction
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        LoggerInterface $logger
     )
    {
       $this->productCollection = $productCollection;
       $this->productAction     = $productAction;
       $this->indexerRegistry   = $indexerRegistry;
       $this->logger            = $logger;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       try {
           $_product = $observer->getProduct();
           
           if(!$_product->getProductnumber())
           {
             $productNumber = rand(100000,999999);
             $collection =  $this->productCollection;
             $collection->addAttributeToSelect(array('productnumber,entity_id'))
                        ->addAttributeToFilter('productnumber',['eq'=>$productNumber]);
             if(count($collection)<1)
             {  
               $this->productAction->updateAttributes(array($_product->getId()), array('productnumber' => $productNumber), 0);
               
             }else{
               $productNumber = rand(100000,999999); 
               $this->productAction->updateAttributes(array($_product->getId()), array('productnumber' => $productNumber), 0); 
               
             }
           }

        try {
              /* if (!empty($_product)) {
                $productIds = [$_product->getId()];
                
                $indexList = [
                    'catalog_category_product', 
                    'catalog_product_category', 
                    'catalogrule_rule',
                    'catalog_product_attribute', 
                    'cataloginventory_stock', 
                    'inventory', 
                    'catalogrule_product', 
                    'catalog_product_price', 
                    'catalogsearch_fulltext'
                ];
 
                foreach ($indexList as $index) {
                    $categoryIndexer = $this->indexerRegistry->get($index);
                
                    //check is indexer is scheduled
                    //if (!$categoryIndexer->isScheduled()) {
                        //$categoryIndexer->reindexList($productIds);
                    //}
                }
             }*/
           } catch (\Exception $e) {
               $this->logger->info($e);
           }
       } catch (\Exception $e) {
           $this->logger->info($e->getMessage());
       }
    }   
}