<?php

namespace Dakha\ColumnAddShipment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
class SalesOrderShipmentAfter implements ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * PlaceOrder constructor.
     */
    public function __construct(
        ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        try {
            $connection = $this->resourceConnection->getConnection();
            $tracksCollection = $shipment->getTracksCollection();
            $carrierName = '';
            foreach ($tracksCollection->getItems() as $track) {
                $carrierName = $track->getTitle();
            }
            if($carrierName)
            {
                 $dataf = [
                 'courier'=>$carrierName
                 ];
                 $where = ['entity_id = ?' => $shipment->getId()];
                 $tableName = $this->resourceConnection->getTableName('sales_shipment');
                 $connection->update($tableName, $dataf, $where);

                 $datas = [
                         'courier'=>$carrierName 
                         ];
                 $where = ['entity_id = ?' => $shipment->getId()];
                 $tableName = $this->resourceConnection->getTableName('sales_shipment_grid');
                 $connection->update($tableName, $datas, $where);  
            }

        } catch (Exception $e) {
            
        }
    }
}