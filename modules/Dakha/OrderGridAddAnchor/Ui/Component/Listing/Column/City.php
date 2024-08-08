<?php
namespace Dakha\OrderGridAddAnchor\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\UrlInterface;
 
class City extends Column
{
    /** Url path */
    
    const ROW_EDIT_URL = 'customer/index/edit/';
    
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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customer;

    /**
     * @var string
     */
    private $_editUrl;
   
    public function __construct(
           ContextInterface $context, 
           UiComponentFactory $uiComponentFactory, 
           StoreManagerInterface $storeManager,
           UrlInterface $urlBuilder,
           OrderRepositoryInterface $orderRepository, 
           SearchCriteriaBuilder $criteria, 
           \Magento\Customer\Model\CustomerFactory $customer,
           array $components = [], 
           array $data = [],
           $editUrl = self::ROW_EDIT_URL
    )
    {
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_editUrl = $editUrl;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_customer = $customer;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                    if (isset($item[$fieldName])) {
                        $storeId = $this->getWebsiteId();
                        $customerModel= $this->_customer->create()->setWebsiteId($storeId)->loadByEmail($item['customer_email']);
                        $userId = $customerModel->getId();
                        $html = "<a  href='" . $this->context->getUrl('customer/index/edit', ['id' => $userId]) . "'>";
                        $html .= $item[$fieldName];
                        $html .= "</a>";
                        $item[$fieldName] = $html;
                    }
            }
        }
        return $dataSource;
    }

    public function getWebsiteId(){
       return $this->_storeManager->getStore()->getWebsiteId();
    }
}