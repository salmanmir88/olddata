<?php
/**
 * Copyright © CourierColumn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CourierColumn\Observer\Sales;
use Magento\Framework\App\ResourceConnection;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Order save after constructor.
     */
    public function __construct(
        ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
/*
        if ($order) {
               $resource   = $this->resourceConnection;
               $connection = $resource->getConnection();  
               $couireName = '';
               $cityName   = $order->getShippingAddress()->getCity();
               $tableName  = $resource->getTableName('courier_manager'); 
               $engSql     = "Select * FROM " . $tableName.' Where city="'.$cityName.'"';
               $engResult  = $connection->fetchRow($engSql);
               
               if($engResult['courier'])
               {
                 $couireName = $engResult['courier'];
               }

               $arbSql = "Select * FROM " . $tableName.' Where city="'.$cityName.'"';
               $arbResult = $connection->fetchRow($arbSql);
               
               if($arbResult['courier'])
               {
                 $couireName = $arbResult['courier'];
               }
               
               $logger->info('courier_manager update '.$couireName);

               $salesordertableName = $resource->getTableName('sales_order');
               $salesordergridtableName = $resource->getTableName('sales_order_grid');
               $sql = "Select * FROM " . $salesordertableName." Where entity_id=".$order->getId()."";
               $resultF = $connection->fetchRow($sql);
               if($resultF)
               {
                  $sql1 = "Update " . $salesordertableName . " Set courier = '".$couireName."' where entity_id=".$order->getId()."";
                  $connection->query($sql1);
               }  

               $sql2 = "Select * FROM " . $salesordergridtableName." Where entity_id=".$order->getId()."";
               $resultS = $connection->fetchRow($sql2);
               if($resultS)
               {
                  $sql3 = "Update " . $salesordergridtableName . " Set courier = '".$couireName."' where entity_id=".$order->getId()."";
                  $connection->query($sql3);
               }  
        }*/

    }
}
