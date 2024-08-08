<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\AddressModify\Observer\Frontend\Sales;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Evince\CourierManager\Model\ResourceModel\Grid\Collection
     */
    protected $cityCollection;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * PlaceOrder constructor.
     */
    public function __construct(
        \Evince\CourierManager\Model\ResourceModel\Grid\Collection $cityCollection,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->cityCollection  = $cityCollection;
        $this->logger          = $logger;
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

            /*$order = $observer->getEvent()->getOrder();
            $shippingAddress = $order->getShippingAddress();
            $city = $shippingAddress->getCity();
            
            $firstCityCollection  = $this->cityCollection->addFieldToFilter('city',['like'=>'%'.$city.'%']);
            
            $englishCity = '';
            $arabicCity = '';
            foreach($firstCityCollection as $engCity)
            {
               $englishCity = $engCity->getCityArabic();
            }

            $secondCityCollection  = $this->cityCollection->addFieldToFilter('city_arabic',['like'=>'%'.$city.'%']);
            
            foreach($firstCityCollection as $arbCity)
            {
               $arabicCity = $engCity->getCity();
            }
            

            if($order->getStoreId()==1 && $arabicCity)
            {
                $order->getShippingAddress()->setCity($arabicCity);
                $order->getBillingAddress()->setCity($arabicCity); 
                $order->save();
            }elseif($order->getStoreId()==2 && $englishCity){
                $order->getShippingAddress()->setCity($englishCity);
                $order->getBillingAddress()->setCity($englishCity);
                $order->save();
            }
*/

        } catch (\Exception $e) { echo $e;die;
            $this->logger->info($e->getMessage());
        }
    }
}

