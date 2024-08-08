<?php /** Product collection filter by custom attribute **/

namespace Eextensions\General\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;

class PreOrderAlbums extends \Magento\Catalog\Block\Product\AbstractProduct {

	/**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';

    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    protected $productCollectionFactory;
    protected $storeManager;
    protected $catalogConfig;
    protected $productVisibility;
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $context->getStoreManager();
        $this->catalogConfig = $context->getCatalogConfig();
        $this->productVisibility = $productVisibility;
        parent::__construct(
            $context,
            $data
        );
    }
	public function getProducts()
    {
		$now = new \DateTime();
		
    	$storeId    = $this->storeManager->getStore()->getId();
		$products = $this->productCollectionFactory->create()->setStoreId($storeId);
		$products
			->addAttributeToSelect($this->catalogConfig->getProductAttributes())
			->addAttributeToFilter($this->getAttibuteCode(), 1)
			->addAttributeToFilter('release_date', ['gt' => $now->format('Y-m-d H:i:s')])
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
            ->addAttributeToSort('updated_at','desc');
        $products->setPageSize($this->getPageSize())->setCurPage(1);
        $products->getSelect()->order('RAND()');
		$this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $products]
        );
       
		return $products;
    }
	
	public function getReleaseDate($data=[]){
		return $data->releaseDate();
		return "01-11-2021";
	}
    
    public function getTitle()
    {
        if (!$this->hasData('title')) {
            $this->setData('title', self::DISPLAY_TYPE_ALL_PRODUCTS);
        }
        return $this->getData('title');
    }
    
    public function getDescription()
    {
        if (!$this->hasData('description')) {
            $this->setData('description', self::DISPLAY_TYPE_ALL_PRODUCTS);
        }
        return $this->getData('description');
    }
    
    
    public function getAttibuteCode()
    {
        if (!$this->hasData('attibute_code')) {
            $this->setData('attibute_code', self::DISPLAY_TYPE_ALL_PRODUCTS);
        }
        return $this->getData('attibute_code');
    }
    
    public function getPageSize()
    {
        if (!$this->hasData('page_size')) {
            $this->setData('page_size', self::DISPLAY_TYPE_ALL_PRODUCTS);
        }
        return $this->getData('page_size');
    }
    
	public function getConfig($att) 
	{
		$path = 'featuredproduct/featuredproduct_config/' . $att;
		return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	function cut_string_featuredproduct($string,$number){
		if(strlen($string) <= $number) {
			return $string;
		}
		else {	
			if(strpos($string," ",$number) > $number){
				$new_space = strpos($string," ",$number);
				$new_string = substr($string,0,$new_space)."..";
				return $new_string;
			}
			$new_string = substr($string,0,$number)."..";
			return $new_string;
		}
	}
	
	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
    public function getProductData($pId = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($pId);
		return $product;
    }
}
