<?php
namespace Dakha\OrderGridAddAnchor\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\UrlInterface;
 
class OrderAnchor extends Column
{
    /** Url path */
    
    const ROW_EDIT_URL = 'sales/order/view/';
    
    /** 
     * @var UrlInterface 
     */

    protected $_urlBuilder;
    
    /** 
     * @var StoreManagerInterface;  
     */
    protected $_storeManager;
    
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;
    
    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteria;

    /**
     * @var string
     */
    private $_editUrl;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
   
    public function __construct(
           ContextInterface $context, 
           UiComponentFactory $uiComponentFactory, 
           StoreManagerInterface $storeManager,
           UrlInterface $urlBuilder,
           OrderRepositoryInterface $orderRepository, 
           SearchCriteriaBuilder $criteria, 
           array $components = [], 
           array $data = [],
           $editUrl = self::ROW_EDIT_URL,
           \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    )
    {
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_editUrl = $editUrl;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {

                    if (isset($item[$fieldName])) {
                        $createdAt = [];
                        $orders = $this->orderCollectionFactory->create()
                                       ->addAttributeToSelect('customer_email')
                                       ->addAttributeToFilter('customer_email', $item['customer_email']);
                        foreach($orders as $order)
                        {
                          $createdAt[] = $order->getCreatedAt();
                        }
                        $days = '';   
                        if(count($orders)>1)
                        {
                            $FirstDate = strtotime($createdAt[0]);
                            $SecondDate = strtotime($createdAt[1]);
                            $datediff = $SecondDate - $FirstDate;
                            $days = round($datediff / (60 * 60 * 24));

                        } 
                        $style = '';
                        if($days<7)
                        { 
                            $style = "style='color:red'";
                        }

                        $html = "<a  ".$style." href='" . $this->context->getUrl('sales/order/view', ['order_id' => $item['entity_id']]) . "'>";
                        $html .= $item[$fieldName];
                        $html .= "</a>";
                        $item[$fieldName] = $html;
                    }

            }
        }
        return $dataSource;
    }
}