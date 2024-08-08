<?php

namespace Dakha\ProductCommonCode\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
class Productsaveafter implements ObserverInterface
{    

    /**
     * @var \Magento\Catalog\Model\Product\Action
    */
    protected $productAction;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Class constructor
     *
     * @param \Magento\Catalog\Model\Product\Action $productAction
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Action $productAction,
        LoggerInterface $logger
     )
    {
       $this->productAction     = $productAction;
       $this->logger            = $logger;

    } 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();
        $catIds = ['3','10','11','12','13','14','15','16','17','18','19','20'];
        $isTrue = false;
        foreach($_product->getCategoryIds() as $catId)
        {
            if(in_array($catId, $catIds))
            {
               $isTrue = true;
               break;
            } 
        }
        \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Psr\Log\LoggerInterface::class)->info('checking logger');
        $this->productAction->updateAttributes(array($_product->getId()), array('product_common_code' => 'PT4KPOP'), 0);
    }   
}