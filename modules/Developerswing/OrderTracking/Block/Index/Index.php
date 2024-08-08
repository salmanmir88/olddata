<?php
/**
 * Copyright © Developerswing All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Developerswing\OrderTracking\Block\Index;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    protected $trackingCollection;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
    protected $orderRepository;
    private $logger;
    protected $_productloader;
    protected $_storeManager;
    protected $reader;
    protected $soapClientFactory;
    protected $scopeConfig;
    protected $orderFactory;
    protected $orderstatusdateFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackingCollection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepository,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Developerswing\OrderTracking\Model\OrderstatusdateFactory $orderstatusdateFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->trackingCollection     = $trackingCollection->create();
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger                 = $logger;
        $this->orderRepository        = $orderRepository;
        $this->productrepository      = $productrepository;
        $this->_storeManager          = $storemanager;
        $this->reader                 = $reader;
        $this->soapClientFactory      = $soapClientFactory;
        $this->scopeConfig            = $scopeConfig;
        $this->orderFactory           = $orderFactory;
        $this->orderstatusdateFactory = $orderstatusdateFactory;
        parent::__construct($context, $data);
    }
    /**
     * @param $trackNumber
     * @return array
     */
  public function getOrderIdsByTrackingNumber()
    {
        $trackNumber = $this->getTrackingNumber(); 
        $this->trackingCollection->addFieldToFilter(
            ShipmentTrackInterface::TRACK_NUMBER,
            $trackNumber
        );

        $ordersData = $this->trackingCollection->getData();
        $result = [];

        foreach ($ordersData as $order) {
            if (isset($order['order_id'])) {
                $result = [
                            'order_id'=>$order['order_id'],
                            'title'=>$order['title'], 
                            'carrier_code'=>$order['carrier_code'],
                            'created_at'=>$order['created_at'],
                            ];
            }
        }

        return $result;
    }

  public function getOrderStatusDates($orderId)
  {
      try {
          $order = $this->orderstatusdateFactory->create()->getCollection()
                              ->addFieldToFilter('order_id',$orderId)
                              ->getFirstItem();
          return $order;                     
      } catch (\Exception $e) {
          $this->logger->critical($e->getMessage());
      }
  }

  public function getOrderInvoiceDate($orderId)
  {
      try {
          $order = $this->orderFactory->create()->load($orderId);
          $invoiceCreatedAt = '';
          foreach ($order->getInvoiceCollection() as $invoice)
            { 
              $invoiceCreatedAt = $invoice->getCreatedAt();
            }
          return $invoiceCreatedAt;
      } catch (\Exception $e) { 
          $this->logger->critical($e->getMessage());
      }
  }

  public function getOrderMessage($status)
  {
      $html = '';
      switch ($status) {
          case "pending1":
            $html = $this->getCommonMessage();
            break;  
          case "pending2":
            $html = $this->getCommonMessage();
            break;
          case "pending3":
            $html = $this->getCommonMessage();
            break;
          case "whatsapp":
            $html = $this->getCommonMessage();
            break;
          case "repeated":
            $html = $this->getCommonMessage();
            break;
          case "closed":
            $html = '<p>The order was returned because the customer did not receive the order from the shipping company</p>';
            break;
          case "holded":
            $html = '<p>Order is on hold</p>';
            break;
          case "ship_via_fall":
            $html = '<p>Order is on hold</p>';
            break;
          case "for_a_courier":
            $html = '<p>Order is on hold</p>';
            break;
          case "prepared_for_jeddah":
            $html = '<p>Order is on hold</p>';
            break;
          case "canceled":
            $html = '<p>Order has been canceled</p>';
            break;
          case "prep_jeddah":
            $html = '<p>Order has been canceled</p>';
            break;
          case "held_at_fetchr":
            $html = '<p>Order has been canceled</p>';
            break;
          case "rewinded":
            $html = '<p>Order has been canceled</p>';
            break;
          case "cancle2":
            $html = '<p>Order has been canceled</p>';
            break;
          case "ship_to_riyadh":
            $html = '<p>order pending</p>';
            break;
          case "transit_makkah":
            $html = '<p>order pending</p>';
            break; 
          case "twice_issue":
            $html = $this->getTwiceIssueCommonMessage();
            break;
          case "sm_issue":
            $html = $this->getSMIssueCommonMessage();
            break;                                   
          default:
            $html = $html = $this->getCommonMessage();
        }
      return $html;  

  }
  
  public function getCommonMessage()
  {
    $storeId = $this->getStoreId();
    if($storeId==1)
    {
      return '<p>Dear valuable customer please contact us via <span style="color: #25D366">WhatsApp</span> <a href="https://wa.me/966570001626/?text=Hello"  target="_blank">966570001626</a> & Kindly Provide us with your correct mobile number / order number so we can be able to deliver your order</p>';
    }elseif($storeId==2){
      return '<p> برقم هاتفك الجوال مع رقم الطلبية حتى نتمكن من<span style="color: #25D366">الواتس</span><a href="https://wa.me/966570001626/?text=Hello"  target="_blank">,+966570001626</a> ، وتزويدنا برقم هاتفك الجوال مع رقم الطلبية حتى نتمكن من توصيل طلبك</p>'; 
    }
  }
  
  public function getTwiceIssueCommonMessage()
  {
    $storeId = $this->getStoreId();
    if($storeId==1)
    {
      return '<p><p style="color:#048ad4">Important notice for Twice Formula Of Love Album:</p> We apologize to you regarding your requests. There is a delay from the factory. The quantity was booked in 1 month. It was confirmed by the factory. We were notified that it will be shipped in the month of 2, but there was a delay until the end of the month 4 according to what the manufacturer said.</p>';
    }elseif($storeId==2){
      return '<p><p style="color:#048ad4">اشعار هام ل البوم Twice Formula Of Love:</p> نعتذر منكم بخصوص طلباتكم هناك تأخير من المصنع وتم حجز الكمية في شهر 1 وتم تأكيده من المصنع وتم اشعارنا انها سوف تنشحن في شهر 2 ولكن حصل تأجيل الى نهاية شهر 4 على حسب كلام المصنع نكرر اعتذارنا ونشكركم على حسن صبركم ونود اعلامكم نسعى جاهدين لتوفير منتجاتكم في أسرع وقت ممكن</p>'; 
    }
  }
  
  public function getSMIssueCommonMessage()
  {
    $storeId = $this->getStoreId();
    if($storeId==1)
    {
      return "<p><p style='color:#048ad4'>Important notice for some SM albums that have been requested:</p>
      In the third month of 2022, a small amount of some bands whose albums had expired were released from the factory for pre-booking to be shipped after their release So far, there is no specific date for the album's delivery from the factory, but there is a possibility that it will be in the month of May 5
      We would like to apologize to you for your order, which has an album that has not yet been delivered from the factory
      The Kibobe Shop team is working on it, and God willing, the first time the products reach us, the order will be shipped to you
      We appreciate your waiting and thank you for your patience
      </p>";
    }elseif($storeId==2){
      return '<p><p style="color:#048ad4">تنبيه هام لبعض الألبومات التي تم طلبها لفرق شركة SM:</p>
      في الشهر الثالث من سنة 2022 تم نزول كمية بسيطة لبعض الفرق التي كانت ألبوماتهم منتهية من المصنع للحجز المسبق ليتم شحنها بعد إصدارها
      والى الان لا يوجد تاريخ محدد لتسليم الألبوم من المصنع ولكن هناك احتمالية انها ستكون في شهر 5 مايو
      ونحب ان نعتذر منك بسبب طلبك الذي به ألبوم لم يتم تسليمه الى الان من المصنع 
      فريق كيبوبية شوب يقومون بالعمل عليه وبأذن الله أول ما تصل لنا المنتجات سيتم شحن الطلب لك
      مقدرين انتظاركم ونشكركم على حسن صبركم 
      </p>'; 
    }
  }
  public function isPreOrder($orderId)
  {
      try {
          $order = $this->orderstatusdateFactory->create()->getCollection()
                              ->addFieldToFilter('order_id',$orderId)
                              ->getFirstItem();
          return $order;                     
      } catch (\Exception $e) {
          $this->logger->critical($e->getMessage());
      }
  }
  public function getTrackingNumberFrom($order)
    {

        $tracksCollection = $order->getTracksCollection();
        $trackNumber      = '';
        $carrierCode      = '';
        $carrierTitle     = '';
        foreach ($tracksCollection->getItems() as $track) {
             $carrierCode  = $track->getCarrierCode();
             $trackNumber  = $track->getTrackNumber();
             $carrierTitle = $track->getTitle(); 
        }
        if($carrierCode=='aramex')
        {
          return $this->getAramexTrackLinkGenerate($trackNumber);
        }elseif ($carrierTitle=='Saee') {
          return 'https://legacy.saeex.com/trackingpage?trackingnum='.$trackNumber;
        }
        return false;
        
    }
  public function getAramexTrackLinkGenerate($trackNumber)
  {
    try {
          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.aramex.com/track/shipments/TrackShippingDetails/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"TrackingNumbers\":[{\"text\":\"".$trackNumber."\"}]}",
            CURLOPT_HTTPHEADER => array(
              "cache-control: no-cache",
              "content-type: application/json",
              "postman-token: c08bcd1a-e55c-524d-e45f-522dfc2a45b1"
            ),
          ));
          $response = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);
          $data = json_decode($response);
          $link = '';
          if(isset($data->Redirect))
          {
            $link = 'https://www.aramex.com/'.$data->Redirect;
          }
          return $link; 
      } catch (\Exception $e) {
          $this->logger->critical($e->getMessage());
      }
      return;
  }  
  public function getTrackingNumber()
    {
        return $this->getRequest()->getParam('search');
    }
  public function getOrderNumber()
    {
        return $this->getRequest()->getParam('search');
    }  
  public function getOrderSearch()
    {
        $order = $this->orderFactory->create()->loadByIncrementId(trim($this->getRequest()->getParam('search')));
        return $this->getOrder($order->getId());
    }

  public function getOrder($orderId)
    {
        try {
           return $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
  public function getShipmentDate($orderId)
  {
     $shipmentDate = '';
     try {
           $order = $this->orderRepository->get($orderId);
           foreach($order->getShipmentsCollection() as $shipment){
			       $shipmentDate = $shipment->getCreatedAt();
			     }
            return $shipmentDate;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        	return $shipmentDate;
        }	
  }
  public function getProductImageUrl($productid)
    {
        try {
           $store   = $this->_storeManager->getStore();
           $product = $this->productrepository->getById($productid);
           return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

    }
    
   public function getMediaUrl()
    {
       $store   = $this->_storeManager->getStore();
       return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'trackorder/';
    }
   public function getProductById($productId)
   {
        try {
           return $this->productrepository->getById($productId);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return false;
   }
   public function getOrderShippingInfo($shippingCarrier,$tracknumber)
   {
     try {
          if($shippingCarrier['carrier_code']=='aramex')
          {
            $order = $this->getOrder($shippingCarrier['order_id']);
            return $this->checkAramexShipmentStatus($order,$tracknumber);
          }       

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }  
   }
  
  public function getReleaseDateCheck($order)
   {
     try {
          $preOrderCheck = false;
          foreach($order->getAllItems() as $_item){
             $product = $this->getProductById($_item->getProductId());
             if($product && $product->getReleaseDate()){
             $releaseDate = strtotime(date('Y-m-d',strtotime($product->getReleaseDate())));
             $todayDate   = strtotime(date('Y-m-d'));

             if($product->getReleaseDate() && $releaseDate > $todayDate && $releaseDate != $todayDate)
             {
               $preOrderCheck = true;
               continue;
             }
           }
          }       
        return $preOrderCheck;  
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

      return false;    
   }

   public function checkAramexShipmentStatus($order,$awb_number) {
        
        $baseUrl = $this->reader->getModuleDir('etc', 'Aramex_Shipping') . '/wsdl/Aramex/';
        $soapClient = $this->soapClientFactory->create($baseUrl .
                'Tracking.wsdl', ['version' => SOAP_1_1, 'trace' => 1, 'keep_alive' => false]);
            
        $params = array(
            'ClientInfo' => array(
                'AccountCountryCode' => $this->scopeConfig->getValue('aramex/settings/account_country_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'AccountEntity' => $this->scopeConfig->getValue('aramex/settings/account_entity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'AccountNumber' => $this->scopeConfig->getValue('aramex/settings/account_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'AccountPin' => $this->scopeConfig->getValue('aramex/settings/account_pin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'UserName' => $this->scopeConfig->getValue('aramex/settings/user_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'Password' => $this->scopeConfig->getValue('aramex/settings/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'Version' => 'v1.0'
            ),
            'Transaction' => array(
                'Reference1' => $order->getIncrementId()
            ),
            'Shipments' => array(
                $awb_number
            )
        );
        
        try {
            $auth_call = $soapClient->TrackShipments($params);
            
            if(!empty($auth_call->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult))
            {   
                $trackingInfo = [];
                if(is_array($auth_call->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult))
                {
                        
                    $transactionResults = $auth_call->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;
                    foreach ($transactionResults as $transaction)
                    {   
                        return $transaction;
                    }
                }else{
                        return $auth_call->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;   
                }

                
            }
            
        } catch (SoapFault $fault) {
            
        }
    }

  public function getStoreId()
  {
      return $this->_storeManager->getStore()->getId();
  }
    
}

