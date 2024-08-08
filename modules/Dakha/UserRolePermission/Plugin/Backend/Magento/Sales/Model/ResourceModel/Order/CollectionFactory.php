<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\UserRolePermission\Plugin\Backend\Magento\Sales\Model\ResourceModel\Order;

use Dakha\UserRolePermission\Model\UserrolesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class CollectionFactory
{
    protected UserrolesFactory $userrolesFactory;
    protected UserContextInterface $userContext;

    public function __construct(
        UserrolesFactory $userrolesFactory,
        UserContextInterface $userContext
     ){
        $this->userrolesFactory = $userrolesFactory;
        $this->userContext = $userContext;
      }

     public function afterGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        $collection,
        $requestName
    ) {
        
        if($requestName != 'sales_order_grid_data_source'){
            return $collection;
        }
        $collection->addFilterToMap('entity_id', 'main_table.entity_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        if ($requestName == 'sales_order_grid_data_source' && $request->getFullActionName()=='sales_order_index' || $request->getFullActionName()=='mui_index_render'){
             if(!empty($request->getParam('search'))){
                return $collection;
             }
             if(!empty($request->getParam('filters')['increment_id'])){
                return $collection;  
             }
             if(!empty($request->getParam('filters')['telephone'])){
                return $collection;  
             }
             if(!empty($request->getParam('filters')['email'])){
                return $collection;  
             }
             if(!empty($request->getParam('filters')['sku'])){
                return $collection;  
             }
             $userId = $this->userContext->getUserId();
             $userRole = $this->userrolesFactory->create()->load($userId,'user_id'); 
             if(empty($userRole->getAllowed())){
                return $collection;  
             }
             $explodeRole = explode(",", $userRole->getAllowed());


             if(count($explodeRole)>1)
             {
                return $this->getOrderCollection($explodeRole,$collection);
             }else{
               switch ($userRole->getAllowed()) {
                 case 'not_invoice':
                     $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                     $connection = $resource->getConnection();
                     $tableName = $resource->getTableName('sales_invoice');

                     $sql = "Select order_id FROM " . $tableName;
                     $result1 = $connection->fetchAll($sql);   
                     $entityIds = [];
                     foreach($result1 as $order)
                     { 
                         $entityIds[] = $order['order_id'];
                     }
                     if(count($entityIds)>0){
                       return $collection->addFieldToFilter('entity_id', array('nin' => [$entityIds]));
                     }
                     break;
                 case 'all_users':
                     break;
                 case 'shipment_after':
                     $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                     $connection = $resource->getConnection();
                     $tableName = $resource->getTableName('sales_shipment');

                     $sql = "Select order_id FROM " . $tableName;
                     $result = $connection->fetchAll($sql);   
                     $entityIds = [];
                     foreach($result as $order)
                     { 
                         $entityIds[] = $order['order_id'];
                     }
                     if(count($entityIds)>0){
                       return $collection->addFieldToFilter('entity_id', array('nin' => [$entityIds]));
                     }
                     break;
                 case 'album':
                     $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
                     $productCollection->addAttributeToSelect('*');
                     $productCollection->addCategoriesFilter(['in' => ['6']]);
                     $skuArr = [];
                     foreach($productCollection as $product)
                     {
                        $skuArr[] = $product->getSku();
                     }

                     $collection->join(
                        ['sales_order_item'],
                        "main_table.entity_id = sales_order_item.order_id",
                        []
                     );
                     return $collection->addFieldToFilter('sku',[$skuArr]);
                     break;            
                 default:
                     break;
               }
             }
            return $collection;
        }
        return $collection;
    }

    public function getOrderCollection($allowed,$collection)
    {
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
       if(in_array('all_users', $allowed)){
          return $collection;
       }elseif (in_array('not_invoice', $allowed) && in_array('shipment_after', $allowed) && in_array('album', $allowed)) {
           $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
           $connection = $resource->getConnection();
           $tableName1 = $resource->getTableName('sales_invoice');
           $sql1 = "Select order_id FROM " . $tableName1;
           $result1 = $connection->fetchAll($sql1);   
           $entityIds1 = [];
           foreach($result1 as $order)
            { 
              $entityIds1[] = $order['order_id'];
            }

           $tableName2 = $resource->getTableName('sales_shipment');
           $sql2 = "Select order_id FROM " . $tableName2;
           $result2 = $connection->fetchAll($sql2);   
           $entityIds2 = [];
           foreach($result2 as $order)
            { 
              $entityIds2[] = $order['order_id'];
            }

           $entityIds3 = $this->getAlbumOrders();
           $mergeentityIds1 = array_merge($entityIds1,$entityIds2);
           $finalMergIncrement = array_merge($entityIds3,$mergeentityIds1);
           $finalUniqueIncrement = array_unique($finalMergIncrement);
           if(count($finalUniqueIncrement)>0)
           {
             return $collection->addFieldToFilter('entity_id', array('in' => [$finalUniqueIncrement]));
           }
       }elseif(in_array('not_invoice', $allowed) && in_array('shipment_after', $allowed)){
           $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
           $connection = $resource->getConnection();
           $tableName1 = $resource->getTableName('sales_invoice');
           $sql1 = "Select order_id FROM " . $tableName1;
           $result1 = $connection->fetchAll($sql1);   
           $entityIds1 = [];
           foreach($result1 as $order)
            { 
              $entityIds1[] = $order['order_id'];
            }

           $tableName2 = $resource->getTableName('sales_shipment');
           $sql2 = "Select order_id FROM " . $tableName2;
           $result2 = $connection->fetchAll($sql2);   
           $entityIds2 = [];
           foreach($result2 as $order)
            { 
              $entityIds2[] = $order['order_id'];
            }
           
           $mergeIncrement1 = array_merge($entityIds1,$entityIds2);
           $finalUniqueIncrement = array_unique($mergeIncrement1);
           if(count($finalUniqueIncrement)>0)
           {
             return $collection->addFieldToFilter('entity_id', array('in' => [$finalUniqueIncrement]));
           }
       }elseif(in_array('not_invoice', $allowed) && in_array('album', $allowed)){
           $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
           $connection = $resource->getConnection();
           $tableName1 = $resource->getTableName('sales_invoice');
           $sql1 = "Select order_id FROM " . $tableName1;
           $result1 = $connection->fetchAll($sql1);   
           $entityIds1 = [];
           foreach($result1 as $order)
            { 
              $entityIds1[] = $order['order_id'];
            }

           $incrementIds2 = $this->getAlbumOrders();
           $mergeIncrement1 = array_merge($entityIds1,$incrementIds2);
           $finalUniqueIncrement = array_unique($mergeIncrement1);
           if(count($finalUniqueIncrement)>0)
           {
             return $collection->addFieldToFilter('entity_id', array('in' => [$finalUniqueIncrement]));
           }
       }elseif(in_array('shipment_after', $allowed) && in_array('album', $allowed)){
           $tableName1 = $resource->getTableName('sales_shipment');
           $sql1 = "Select order_id FROM " . $tableName1;
           $result1 = $connection->fetchAll($sql1);   
           $entityIds1 = [];
           foreach($result1 as $order)
            { 
              $entityIds1[] = $order['order_id'];
            }
           $incrementIds2 = $this->getAlbumOrders();
           $mergeIncrement1 = array_merge($entityIds1,$incrementIds2);
           $finalUniqueIncrement = array_unique($mergeIncrement1);
           if(count($finalUniqueIncrement)>0)
           {
             return $collection->addFieldToFilter('entity_id', array('in' => [$finalUniqueIncrement]));
           }               
       }
    }

    public function getAlbumOrders()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addCategoriesFilter(['in' => ['6']]);
        $skuArr = [];
        foreach($productCollection as $product)
         {
            $skuArr[] = $product->getSku();
         }
        $orderCollectionFactory = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create();
        $orderCollection = $orderCollectionFactory
                        ->addAttributeToSelect("*");
        $orderCollection->join(
                        ['sales_order_item'],
                        "main_table.entity_id = sales_order_item.order_id",
                        []
                     );
        $orderCollection->addFieldToFilter('sku',[$skuArr]); 
        $orderCollection->setOrder('created_at','desc');  
        $incrementIds = [];
        foreach($orderCollection as $order)
        {
           $incrementIds[] = $order->getId(); 
        }
        return $incrementIds;
    }
}