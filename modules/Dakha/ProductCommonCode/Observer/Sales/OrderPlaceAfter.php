<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\ProductCommonCode\Observer\Sales;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * PlaceOrder constructor.
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->_productloader = $_productloader;
        $this->logger  = $logger;
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
        try {
             
             $order = $observer->getEvent()->getOrder();
             $orderItems = $order->getAllItems();
             foreach ($orderItems as $item)
             {
                $_product = $this->_productloader->create()->setStoreId($order->getStoreId())->load($item->getProductId());
                
                $connection = $this->resourceConnection->getConnection(); 
                $data = ["product_common_code"=>$_product->getProductCommonCode(),
                         "product_model"=>$_product->getProductModel(),
                         "version"=>$_product->getVersion(),
                         "album_qyt"=>$_product->getAlbumQyt(),
                         "is_featured"=>$_product->getIsFeatured(),
                         "upc"=>$_product->getUpc(),
                         "Link"=>$_product->getLink(),
                         "barcode"=>$_product->getBarcode(),
                        ]; 
                $where = ['sku = ?' => $_product->getSku()];
                $tableName = $this->resourceConnection->getTableName('sales_order_item');
                //$connection->update($tableName, $data, $where);

                //$item->setProductCommonCode($_product->getProductCommonCode());
                //$item->save();
                       
             }

        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}

